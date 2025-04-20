<?php

/** @noinspection SqlNoDataSourceInspection */

declare(strict_types=1);

namespace Rancoud\Database;

class Database
{
    /** @var Configurator|null Configurator */
    protected ?Configurator $configurator = null;

    /** @var \PDO|null PDO */
    protected ?\PDO $pdo = null;

    /** @var array Errors */
    protected array $errors = [];

    /** @var array Saved queries */
    protected array $savedQueries = [];

    /** @var array Instances of databases */
    protected static array $instances = [];

    /** @var string[] List of drivers that support nested transactions */
    protected array $nestedTransactionsDriverSupported = ['mysql', 'pgsql', 'sqlite'];

    /** @var int Transaction depth */
    protected int $transactionDepth = 0;

    public function __construct(Configurator $configurator)
    {
        $this->configurator = $configurator;
    }

    /**
     * Sets database instances.
     *
     * @throws DatabaseException
     */
    public static function setInstance(Configurator $configurator, string $name = 'primary'): self
    {
        if (isset(static::$instances[$name])) {
            throw new DatabaseException('Cannot overwrite instance "' . $name . '"');
        }

        static::$instances[$name] = new static($configurator);

        return static::$instances[$name];
    }

    /** Returns if database instances exists. */
    public static function hasInstance(string $name = 'primary'): bool
    {
        return isset(static::$instances[$name]);
    }

    /**
     * Returns database instance.
     *
     * @return static|null
     */
    public static function getInstance(string $name = 'primary'): ?self
    {
        return static::$instances[$name] ?? null;
    }

    /** Returns if nested transaction is supported. */
    public function isNestedTransactionSupported(): bool
    {
        return \in_array($this->configurator->getDriver(), $this->nestedTransactionsDriverSupported, true);
    }

    /**
     * Connect to the database.
     *
     * @throws DatabaseException
     */
    public function connect(): void
    {
        try {
            $startTime = \microtime(true);

            $this->pdo = $this->configurator->createPDOConnection();

            $endTime = \microtime(true);

            if ($this->configurator->hasSavedQueries()) {
                $this->savedQueries[] = ['Connection' => $this->getTime($startTime, $endTime)];
            }
        } catch (\Exception $e) {
            $this->addErrorConnection($e);

            throw new DatabaseException('Error Connecting Database');
        }
    }

    /**
     * Returns PDOStatement after binding parameter key to value in sql query.
     *
     * @throws DatabaseException
     */
    protected function prepareBind(string $sql, array $parameters = []): \PDOStatement
    {
        if ($this->pdo === null) {
            $this->connect();
        }

        try {
            $statement = $this->pdo->prepare($sql);
            if ($statement === false) {
                throw new DatabaseException('Error Prepare Statement');
            }
        } catch (\Exception) {
            $this->addErrorPrepare($sql, $parameters);

            throw new DatabaseException('Error Prepare Statement');
        }

        foreach ($parameters as $key => $value) {
            $param = $this->getPdoParamType($value);
            if (\is_float($value)) {
                $value = (string) $value;
            }

            if ($param === false) {
                $this->addErrorPrepare($sql, $parameters);

                throw new DatabaseException('Error Bind Value');
            }

            try {
                $success = $statement->bindValue(":{$key}", $value, $param);
                if ($success === false) {
                    throw new DatabaseException('Error Bind Value');
                }
                // @codeCoverageIgnoreStart
            } catch (\Exception) {
                // Could not reach this statement without mocking database
                $this->addErrorPrepare($sql, $parameters);

                throw new DatabaseException('Error Bind Value');
            }
            // @codeCoverageIgnoreEnd
        }

        return $statement;
    }

    /** Returns PDO param type according to value type. */
    protected function getPdoParamType(mixed $value): bool|int
    {
        if (\is_int($value)) {
            return \PDO::PARAM_INT;
        }

        if (\is_bool($value)) {
            return \PDO::PARAM_BOOL;
        }

        if ($value === null) {
            return \PDO::PARAM_NULL;
        }

        if (\is_string($value)) {
            return \PDO::PARAM_STR;
        }

        if (\is_float($value)) {
            return \PDO::PARAM_STR;
        }

        if (\is_resource($value)) {
            return \PDO::PARAM_LOB;
        }

        return false;
    }

    /** Add error statement in errors. */
    protected function addErrorStatement(\PDOStatement $statement): void
    {
        $this->errors[] = [
            'query'       => $statement->queryString,
            'query_error' => $statement->errorInfo(),
            'pdo_error'   => $this->pdo->errorInfo(),
            'dump_params' => $this->getDumpParams($statement)
        ];
    }

    /** Add error connection in errors. */
    protected function addErrorConnection(\Exception $exception): void
    {
        $this->errors[] = [
            'query'       => $this->configurator->getDSN(),
            'query_error' => null,
            'pdo_error'   => $exception->getMessage(),
            'dump_params' => $this->configurator->getParametersForPDO()
        ];
    }

    /** Add error prepare in errors. */
    protected function addErrorPrepare(string $sql, array $parameters): void
    {
        $this->errors[] = [
            'query'       => $sql,
            'query_error' => null,
            'pdo_error'   => $this->pdo->errorInfo(),
            'dump_params' => $parameters
        ];
    }

    /** Add query in saved queries. */
    protected function addQuery(\PDOStatement $statement, array $parameters, float $time): void
    {
        if ($this->configurator->hasSavedQueries()) {
            $this->savedQueries[] = [
                'query'      => $statement->queryString,
                'parameters' => $parameters,
                'time'       => $time
            ];
        }
    }

    /** Returns dump parameters. */
    protected function getDumpParams(\PDOStatement $statement): string
    {
        \ob_start();
        $statement->debugDumpParams();

        return \ob_get_clean();
    }

    /**
     * Query Select.
     *
     * @throws DatabaseException
     */
    public function select(string $sql, array $parameters = []): \PDOStatement
    {
        $statement = $this->prepareBind($sql, $parameters);

        $startTime = \microtime(true);

        $this->executeStatement($statement);

        $endTime = \microtime(true);

        $this->addQuery($statement, $parameters, $this->getTime($startTime, $endTime));

        return $statement;
    }

    /**
     * Execute Statement.
     *
     * @throws DatabaseException
     */
    protected function executeStatement(\PDOStatement $statement): void
    {
        try {
            $success = $statement->execute();
            if ($success === false) {
                throw new DatabaseException('Error Execute');
            }
        } catch (\Exception) {
            $this->addErrorStatement($statement);

            throw new DatabaseException('Error Execute');
        }
    }

    /** Read. */
    public function read(\PDOStatement $statement, int $fetchType = \PDO::FETCH_ASSOC): mixed
    {
        return $statement->fetch($fetchType);
    }

    /** Read all. */
    public function readAll(\PDOStatement $statement, int $fetchType = \PDO::FETCH_ASSOC): array
    {
        return $statement->fetchAll($fetchType);
    }

    /**
     * Insert.
     *
     * @throws DatabaseException
     */
    public function insert(string $sql, array $parameters = [], bool $getLastInsertId = false): ?int
    {
        $statement = $this->prepareBind($sql, $parameters);

        $startTime = \microtime(true);

        $this->executeStatement($statement);

        $endTime = \microtime(true);

        $this->addQuery($statement, $parameters, $this->getTime($startTime, $endTime));

        $lastInsertId = null;
        if ($getLastInsertId === true) {
            $lastInsertId = (int) $this->pdo->lastInsertId();
        }

        $statement->closeCursor();
        $statement = null;

        return $lastInsertId;
    }

    /**
     * Update.
     *
     * @throws DatabaseException
     */
    public function update(string $sql, array $parameters = [], bool $getAffectedRowsCount = false): ?int
    {
        $statement = $this->prepareBind($sql, $parameters);

        $startTime = \microtime(true);

        $this->executeStatement($statement);

        $endTime = \microtime(true);

        $this->addQuery($statement, $parameters, $this->getTime($startTime, $endTime));

        $affectedRowsCount = null;
        if ($getAffectedRowsCount) {
            $affectedRowsCount = $statement->rowCount();
        }

        $statement->closeCursor();
        $statement = null;

        return $affectedRowsCount;
    }

    /**
     * Delete.
     *
     * @throws DatabaseException
     */
    public function delete(string $sql, array $parameters = [], bool $getAffectedRowsCount = false): ?int
    {
        $statement = $this->prepareBind($sql, $parameters);

        $startTime = \microtime(true);

        $this->executeStatement($statement);

        $endTime = \microtime(true);

        $this->addQuery($statement, $parameters, $this->getTime($startTime, $endTime));

        $affectedRowsCount = null;
        if ($getAffectedRowsCount) {
            $affectedRowsCount = $statement->rowCount();
        }

        $statement->closeCursor();
        $statement = null;

        return $affectedRowsCount;
    }

    /**
     * Count.
     *
     * @throws DatabaseException
     */
    public function count(string $sql, array $parameters = []): ?int
    {
        $statement = $this->select($sql, $parameters);

        $cursor = $statement->fetch(\PDO::FETCH_ASSOC);

        $count = (int) \current($cursor);

        $statement->closeCursor();
        $statement = null;

        return $count;
    }

    /**
     * Execute query.
     *
     * @throws DatabaseException
     */
    public function exec(string $sql, array $parameters = []): void
    {
        $statement = $this->prepareBind($sql, $parameters);

        $startTime = \microtime(true);

        $this->executeStatement($statement);

        $endTime = \microtime(true);

        $this->addQuery($statement, $parameters, $this->getTime($startTime, $endTime));

        $statement->closeCursor();
        $statement = null;
    }

    /** Returns PDO object. */
    public function getPDO(): ?\PDO
    {
        return $this->pdo;
    }

    /**
     * Select all.
     *
     * @throws DatabaseException
     */
    public function selectAll(string $sql, array $parameters = []): array
    {
        $statement = $this->select($sql, $parameters);

        $results = $this->readAll($statement);

        $statement->closeCursor();
        $statement = null;

        return $results;
    }

    /**
     * Select row.
     *
     * @throws DatabaseException
     */
    public function selectRow(string $sql, array $parameters = []): array
    {
        $statement = $this->select($sql, $parameters);

        $row = $this->read($statement);
        if ($row === false) {
            $row = [];
        }

        $statement->closeCursor();
        $statement = null;

        return $row;
    }

    /**
     * Select column.
     *
     * @throws DatabaseException
     */
    public function selectCol(string $sql, array $parameters = []): array
    {
        $statement = $this->select($sql, $parameters);

        $data = $this->readAll($statement);
        $col = [];
        foreach ($data as $row) {
            $col[] = \current($row);
        }

        $statement->closeCursor();
        $statement = null;

        return $col;
    }

    /**
     * Select variable.
     *
     * @throws DatabaseException
     */
    public function selectVar(string $sql, array $parameters = []): mixed
    {
        $statement = $this->select($sql, $parameters);

        $row = $this->read($statement);
        if ($row === false) {
            $var = false;
        } else {
            $var = \current($row);
        }

        $statement->closeCursor();
        $statement = null;

        return $var;
    }

    /**
     * Start transaction.
     *
     * @throws DatabaseException
     */
    public function startTransaction(): void
    {
        if ($this->pdo === null) {
            $this->connect();
        }

        try {
            if ($this->transactionDepth === 0 || $this->isNestedTransactionSupported() === false) {
                if ($this->pdo->beginTransaction() === false) {
                    throw new DatabaseException('Error Begin Transaction');
                }
            } else {
                $this->exec('SAVEPOINT LEVEL' . $this->transactionDepth);
            }

            ++$this->transactionDepth;
            // @codeCoverageIgnoreStart
        } catch (\Exception) {
            // Could not reach this statement without mocking database
            throw new DatabaseException('Error Begin Transaction');
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Complete transaction.
     *
     * @throws DatabaseException
     */
    public function completeTransaction(): void
    {
        if ($this->hasErrors()) {
            $this->rollbackTransaction();
        } else {
            $this->commitTransaction();
        }
    }

    /**
     * Commit transaction.
     *
     * @throws DatabaseException
     */
    public function commitTransaction(): void
    {
        if ($this->pdo === null || $this->transactionDepth === 0 || $this->pdo->inTransaction() === false) {
            throw new DatabaseException('Error No Transaction started');
        }

        try {
            --$this->transactionDepth;

            if ($this->transactionDepth === 0 || $this->isNestedTransactionSupported() === false) {
                if ($this->pdo->commit() === false) {
                    throw new DatabaseException('Error Commit Transaction');
                }
            } else {
                $this->exec('RELEASE SAVEPOINT LEVEL' . $this->transactionDepth);
            }
            // @codeCoverageIgnoreStart
        } catch (\Exception) {
            // Could not reach this statement without mocking database
            throw new DatabaseException('Error Commit Transaction');
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Rollback transaction.
     *
     * @throws DatabaseException
     */
    public function rollbackTransaction(): void
    {
        if ($this->pdo === null || $this->transactionDepth === 0 || $this->pdo->inTransaction() === false) {
            throw new DatabaseException('Error No Transaction started');
        }

        try {
            --$this->transactionDepth;

            if ($this->transactionDepth === 0 || $this->isNestedTransactionSupported() === false) {
                if ($this->pdo->rollBack() === false) {
                    throw new DatabaseException('Error Rollback Transaction');
                }
            } else {
                $this->exec('ROLLBACK TO SAVEPOINT LEVEL' . $this->transactionDepth);
            }
            // @codeCoverageIgnoreStart
        } catch (\Exception) {
            // Could not reach this statement without mocking database
            throw new DatabaseException('Error Rollback Transaction');
        }
        // @codeCoverageIgnoreEnd
    }

    /** Returns if has errors. */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /** Returns errors. */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /** Return last error. */
    public function getLastError(): ?array
    {
        $countErrors = \count($this->errors);
        if ($countErrors === 0) {
            return null;
        }

        return $this->errors[$countErrors - 1];
    }

    /** Wipe errors. */
    public function cleanErrors(): void
    {
        $this->errors = [];
    }

    /** Returns if has save queries. */
    public function hasSaveQueries(): bool
    {
        return $this->configurator->hasSavedQueries();
    }

    /** Enable save queries. */
    public function enableSaveQueries(): void
    {
        $this->configurator->enableSaveQueries();
    }

    /** Disable save queries. */
    public function disableSaveQueries(): void
    {
        $this->configurator->disableSaveQueries();
    }

    /** Wipe save queries. */
    public function cleanSavedQueries(): void
    {
        $this->savedQueries = [];
    }

    /** Returns save queries. */
    public function getSavedQueries(): array
    {
        return $this->savedQueries;
    }

    /**
     * Truncate tables.
     *
     * @param array $tables
     *
     * @throws DatabaseException
     */
    public function truncateTables(string $tableMandatory, string ...$tables): void
    {
        \array_unshift($tables, $tableMandatory);

        $isSqlite = ($this->configurator->getDriver() === 'sqlite');
        foreach ($tables as $table) {
            $sql = 'TRUNCATE TABLE ' . $table;
            if ($isSqlite) {
                $sql = 'DELETE FROM ' . $table;
            }

            $this->exec($sql);
        }
    }

    /**
     * Drop Tables.
     *
     * @param array $tables
     *
     * @throws DatabaseException
     */
    public function dropTables(string $tableMandatory, string ...$tables): void
    {
        \array_unshift($tables, $tableMandatory);

        if ($this->configurator->getDriver() === 'sqlite') {
            foreach ($tables as $table) {
                $sql = 'DROP TABLE IF EXISTS ' . $table;
                $this->exec($sql);
            }

            return;
        }

        $tables = \implode(',', $tables);

        $sql = 'DROP TABLE IF EXISTS ' . $tables;
        $this->exec($sql);
    }

    /**
     * Read SQL file to execute.
     *
     * @throws DatabaseException
     */
    public function useSqlFile(string $filepath): void
    {
        if (!\file_exists($filepath) || !\is_file($filepath)) {
            throw new DatabaseException('File missing for useSqlFile method: ' . $filepath);
        }

        if (!\is_readable($filepath)) {
            // @codeCoverageIgnoreStart
            // Could not reach this statement without mocking filesystem
            throw new DatabaseException('File is not readable for useSqlFile method: ' . $filepath);
            // @codeCoverageIgnoreEnd
        }

        $sqlFile = \file_get_contents($filepath);

        if ($this->configurator->getDriver() === 'sqlite') {
            // sqlite support only one statement by exec
            $sqlFileQueries = \preg_split('/;\R/', $sqlFile);
            foreach ($sqlFileQueries as $sqlFileQuery) {
                if ($sqlFileQuery === '') {
                    continue;
                }

                $this->exec($sqlFileQuery);
            }

            return;
        }

        $this->exec($sqlFile);
    }

    /** Returns elapsed time. */
    protected function getTime(float $startTime, float $endTime): float
    {
        return \round(($endTime - $startTime) * 1000000) / 1000000;
    }

    /** Sets PDO object to null to disconnect from database. */
    public function disconnect(): void
    {
        $this->pdo = null;
    }
}
