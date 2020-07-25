<?php

declare(strict_types=1);

namespace Rancoud\Database;

use Exception;
use PDO;
use PDOStatement;

/**
 * Class Database.
 */
class Database
{
    /** @var Configurator|null */
    protected ?Configurator $configurator = null;

    /** @var PDO|null */
    protected ?PDO $pdo = null;

    /** @var array */
    protected array $errors = [];

    /** @var array */
    protected array $savedQueries = [];

    /** @var Database|null */
    protected static ?Database $instance = null;

    /** @var string[] */
    protected array $nestedTransactionsDriverSupported = ['mysql', 'pgsql', 'sqlite'];

    /** @var int */
    protected int $transactionDepth = 0;

    /**
     * Database constructor.
     *
     * @param Configurator $configurator
     */
    public function __construct(Configurator $configurator)
    {
        $this->configurator = $configurator;
    }

    /**
     * @param Configurator|null $configurator
     *
     * @throws DatabaseException
     *
     * @return Database
     */
    public static function getInstance(Configurator $configurator = null): self
    {
        if (static::$instance === null) {
            if ($configurator === null) {
                throw new DatabaseException('Configurator Missing');
            }
            static::$instance = new static($configurator);
        } elseif ($configurator !== null) {
            throw new DatabaseException('Configurator Already Setup');
        }

        return static::$instance;
    }

    public function isNestedTransactionSupported(): bool
    {
        return \in_array($this->configurator->getDriver(), $this->nestedTransactionsDriverSupported, true);
    }

    /**
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
        } catch (Exception $e) {
            $this->addErrorConnection($e);
            throw new DatabaseException('Error Connecting Database');
        }
    }

    /**
     * @param string $sql
     * @param array  $parameters
     *
     * @throws DatabaseException
     *
     * @return PDOStatement
     */
    protected function prepareBind(string $sql, array $parameters = []): PDOStatement
    {
        if ($this->pdo === null) {
            $this->connect();
        }

        try {
            $statement = $this->pdo->prepare($sql);
            if ($statement === false) {
                /* @noinspection ThrowRawExceptionInspection */
                throw new Exception('Error Prepare Statement');
            }
        } catch (Exception $e) {
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
                $success = $statement->bindValue(":$key", $value, $param);
                if ($success === false) {
                    /* @noinspection ThrowRawExceptionInspection */
                    throw new Exception('Error Bind Value');
                }
                // @codeCoverageIgnoreStart
            } catch (Exception $e) {
                $this->addErrorPrepare($sql, $parameters);
                throw new DatabaseException('Error Bind Value');
            }
            // @codeCoverageIgnoreEnd
        }

        return $statement;
    }

    /**
     * @param mixed $value
     *
     * @return bool|int
     */
    protected function getPdoParamType($value)
    {
        if (\is_int($value)) {
            return PDO::PARAM_INT;
        }

        if (\is_bool($value)) {
            return PDO::PARAM_BOOL;
        }

        if ($value === null) {
            return PDO::PARAM_NULL;
        }

        if (\is_string($value)) {
            return PDO::PARAM_STR;
        }

        if (\is_float($value)) {
            return PDO::PARAM_STR;
        }

        if (\is_resource($value)) {
            return PDO::PARAM_LOB;
        }

        return false;
    }

    protected function addErrorStatement(PDOStatement $statement): void
    {
        $this->errors[] = [
            'query'       => $statement->queryString,
            'query_error' => $statement->errorInfo(),
            'pdo_error'   => $this->pdo->errorInfo(),
            'dump_params' => $this->getDumpParams($statement)
        ];
    }

    protected function addErrorConnection(Exception $exception): void
    {
        $this->errors[] = [
            'query'       => $this->configurator->getDsn(),
            'query_error' => null,
            'pdo_error'   => $exception->getMessage(),
            'dump_params' => $this->configurator->getParametersForPDO()
        ];
    }

    protected function addErrorPrepare(string $sql, array $parameters): void
    {
        $this->errors[] = [
            'query'       => $sql,
            'query_error' => null,
            'pdo_error'   => $this->pdo->errorInfo(),
            'dump_params' => $parameters
        ];
    }

    protected function addQuery(PDOStatement $statement, array $parameters, float $time): void
    {
        if ($this->configurator->hasSavedQueries()) {
            $this->savedQueries[] = [
                'query'      => $statement->queryString,
                'parameters' => $parameters,
                'time'       => $time
            ];
        }
    }

    protected function getDumpParams(PDOStatement $statement): string
    {
        \ob_start();
        $statement->debugDumpParams();

        return \ob_get_clean();
    }

    /**
     * @param string $sql
     * @param array  $parameters
     *
     * @throws DatabaseException
     *
     * @return PDOStatement
     */
    public function select(string $sql, array $parameters = []): PDOStatement
    {
        $statement = $this->prepareBind($sql, $parameters);

        $startTime = \microtime(true);

        $this->executeStatement($statement);

        $endTime = \microtime(true);

        $this->addQuery($statement, $parameters, $this->getTime($startTime, $endTime));

        return $statement;
    }

    /**
     * @param PDOStatement $statement
     *
     * @throws DatabaseException
     */
    protected function executeStatement(PDOStatement $statement): void
    {
        try {
            $success = $statement->execute();
            if ($success === false) {
                /* @noinspection ThrowRawExceptionInspection */
                throw new Exception('Error Execute');
            }
        } catch (Exception $e) {
            $this->addErrorStatement($statement);
            throw new DatabaseException('Error Execute');
        }
    }

    /**
     * @param PDOStatement $statement
     * @param int          $fetchType
     *
     * @return mixed
     */
    public function read(PDOStatement $statement, $fetchType = PDO::FETCH_ASSOC)
    {
        return $statement->fetch($fetchType);
    }

    /**
     * @param PDOStatement $statement
     * @param int          $fetchType
     *
     * @return array
     */
    public function readAll(PDOStatement $statement, $fetchType = PDO::FETCH_ASSOC): array
    {
        return $statement->fetchAll($fetchType);
    }

    /**
     * @param string $sql
     * @param array  $parameters
     * @param bool   $getLastInsertId
     *
     * @throws DatabaseException
     *
     * @return int|null
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
     * @param string $sql
     * @param array  $parameters
     * @param bool   $getAffectedRowsCount
     *
     * @throws DatabaseException
     *
     * @return int|null
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
            $affectedRowsCount = (int) $statement->rowCount();
        }

        $statement->closeCursor();
        $statement = null;

        return $affectedRowsCount;
    }

    /**
     * @param string $sql
     * @param array  $parameters
     * @param bool   $getAffectedRowsCount
     *
     * @throws DatabaseException
     *
     * @return int|null
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
            $affectedRowsCount = (int) $statement->rowCount();
        }

        $statement->closeCursor();
        $statement = null;

        return $affectedRowsCount;
    }

    /**
     * @param string $sql
     * @param array  $parameters
     *
     * @throws DatabaseException
     *
     * @return int|null
     */
    public function count(string $sql, array $parameters = []): ?int
    {
        $statement = $this->select($sql, $parameters);

        $cursor = $statement->fetch(PDO::FETCH_ASSOC);

        $count = (int) \current($cursor);

        $statement->closeCursor();
        $statement = null;

        return $count;
    }

    /**
     * @param string $sql
     * @param array  $parameters
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

    public function getPdo(): ?PDO
    {
        return $this->pdo;
    }

    /**
     * @param string $sql
     * @param array  $parameters
     *
     * @throws DatabaseException
     *
     * @return array
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
     * @param string $sql
     * @param array  $parameters
     *
     * @throws DatabaseException
     *
     * @return array
     */
    public function selectRow($sql, array $parameters = []): array
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
     * @param string $sql
     * @param array  $parameters
     *
     * @throws DatabaseException
     *
     * @return array
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
     * @param string $sql
     * @param array  $parameters
     *
     * @throws DatabaseException
     *
     * @return mixed
     */
    public function selectVar(string $sql, array $parameters = [])
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
                    /* @noinspection ThrowRawExceptionInspection */
                    throw new Exception('Error Begin Transaction');
                }
            } else {
                $this->exec('SAVEPOINT LEVEL' . $this->transactionDepth);
            }

            ++$this->transactionDepth;
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DatabaseException('Error Begin Transaction');
        }
        // @codeCoverageIgnoreEnd
    }

    /**
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
                    /* @noinspection ThrowRawExceptionInspection */
                    throw new Exception('Error Commit Transaction');
                }
            } else {
                $this->exec('RELEASE SAVEPOINT LEVEL' . $this->transactionDepth);
            }
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DatabaseException('Error Commit Transaction');
        }
        // @codeCoverageIgnoreEnd
    }

    /**
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
                    /* @noinspection ThrowRawExceptionInspection */
                    throw new Exception('Error Rollback Transaction');
                }
            } else {
                $this->exec('ROLLBACK TO SAVEPOINT LEVEL' . $this->transactionDepth);
            }
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DatabaseException('Error Rollback Transaction');
        }
        // @codeCoverageIgnoreEnd
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getLastError(): ?array
    {
        $countErrors = \count($this->errors);
        if ($countErrors === 0) {
            return null;
        }

        return $this->errors[$countErrors - 1];
    }

    public function cleanErrors(): void
    {
        $this->errors = [];
    }

    public function hasSaveQueries(): bool
    {
        return $this->configurator->hasSavedQueries();
    }

    public function enableSaveQueries(): void
    {
        $this->configurator->enableSaveQueries();
    }

    public function disableSaveQueries(): void
    {
        $this->configurator->disableSaveQueries();
    }

    public function cleanSavedQueries(): void
    {
        $this->savedQueries = [];
    }

    public function getSavedQueries(): array
    {
        return $this->savedQueries;
    }

    /**
     * @param string $tableMandatory
     * @param array  $tables
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
     * @param string $tableMandatory
     * @param array  $tables
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

        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $tables = \implode(',', $tables);

        $sql = 'DROP TABLE IF EXISTS ' . $tables;
        $this->exec($sql);
    }

    /**
     * @param string $filepath
     *
     * @throws DatabaseException
     */
    public function useSqlFile(string $filepath): void
    {
        if (!\file_exists($filepath)) {
            throw new DatabaseException('File missing for useSqlFile method: ' . $filepath);
        }
        if (!\is_readable($filepath)) {
            throw new DatabaseException('File is not readable for useSqlFile method: ' . $filepath);
        }

        $sqlFile = \file_get_contents($filepath);

        if ($this->configurator->getDriver() === 'sqlite') {
            // sqlite support only one statement by exec
            $sqlFileQueries = \explode(";\n", $sqlFile);
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

    protected function getTime(float $startTime, float $endTime): float
    {
        return \round(($endTime - $startTime) * 1000000) / 1000000;
    }

    public function disconnect(): void
    {
        $this->pdo = null;
    }
}
