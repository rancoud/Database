<?php

declare(strict_types=1);

namespace Rancoud\Database;

use Exception;
use PDO;
use PDOException;
use PDOStatement;

/**
 * Class Database.
 */
class Database
{
    /**
     * @var Configurator|null
     */
    protected ?Configurator $configurator = null;

    /**
     * @var PDO|null
     */
    protected ?PDO $pdo = null;

    /**
     * @var array
     */
    protected array $errors = [];

    /**
     * @var array
     */
    protected array $savedQueries = [];

    /**
     * @var Database|null
     */
    protected static ?Database $instance = null;

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

    /**
     * @throws DatabaseException
     */
    public function connect(): void
    {
        try {
            $startTime = \microtime(true);

            $this->pdo = $this->configurator->createPDOConnection();

            $endTime = \microtime(true);

            if ($this->configurator->hasSaveQueries()) {
                $this->savedQueries[] = ['Connection' => $this->getTime($startTime, $endTime)];
            }
        } catch (Exception $e) {
            $this->addErrorConnection($e);

            if ($this->configurator->hasThrowException()) {
                throw new DatabaseException('Error Connecting Database');
            }
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
    protected function prepareBind(string $sql, array $parameters = []): ?PDOStatement
    {
        if ($this->pdo === null) {
            $this->connect();
        }

        $statement = null;

        try {
            $statement = $this->pdo->prepare($sql);
            if ($statement === false) {
                /* @noinspection ThrowRawExceptionInspection */
                throw new Exception('Error Prepare Statement');
            }
        } catch (Exception $e) {
            $this->addErrorPrepare($sql, $parameters);
            if ($this->configurator->hasThrowException()) {
                throw new DatabaseException('Error Prepare Statement');
            }
        }

        if ($statement === false) {
            return null;
        }

        foreach ($parameters as $key => $value) {
            $param = $this->getPdoParamType($value);
            if (\is_float($value)) {
                $value = (string) $value;
            }

            if ($param === false) {
                throw new DatabaseException('Error Bind Value');
            }

            try {
                $statement->bindValue(":$key", $value, $param);
            } catch (PDOException $e) {
                throw new DatabaseException($e->getMessage());
            }
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

        if (null === $value) {
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
        if ($this->configurator->hasSaveQueries()) {
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
     * @return PDOStatement|null
     */
    public function select(string $sql, array $parameters = []): ?PDOStatement
    {
        $statement = $this->prepareBind($sql, $parameters);

        if ($statement === null) {
            return null;
        }

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
     *
     * @return bool
     */
    protected function executeStatement(PDOStatement $statement): bool
    {
        $success = false;

        try {
            $success = $statement->execute();
            if ($success === false) {
                /* @noinspection ThrowRawExceptionInspection */
                throw new Exception('Error Execute');
            }
        } catch (Exception $e) {
            $this->addErrorStatement($statement);
            if ($this->configurator->hasThrowException()) {
                throw new DatabaseException('Error Execute');
            }
        }

        return $success;
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
     * @return int|bool
     */
    public function insert(string $sql, array $parameters = [], bool $getLastInsertId = false)
    {
        $statement = $this->prepareBind($sql, $parameters);

        if ($statement === null) {
            return false;
        }

        $startTime = \microtime(true);

        $success = $this->executeStatement($statement);
        if ($success === false) {
            return false;
        }

        $endTime = \microtime(true);

        $this->addQuery($statement, $parameters, $this->getTime($startTime, $endTime));

        $lastInsertId = true;
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
     * @param bool   $getCountRowsAffected
     *
     * @throws DatabaseException
     *
     * @return int|bool
     */
    public function update(string $sql, array $parameters = [], bool $getCountRowsAffected = false)
    {
        $statement = $this->prepareBind($sql, $parameters);

        if ($statement === null) {
            return false;
        }

        $startTime = \microtime(true);

        $success = $this->executeStatement($statement);
        if ($success === false) {
            return false;
        }

        $endTime = \microtime(true);

        $this->addQuery($statement, $parameters, $this->getTime($startTime, $endTime));

        $countRowAffected = true;
        if ($getCountRowsAffected) {
            $countRowAffected = (int) $statement->rowCount();
        }

        $statement->closeCursor();
        $statement = null;

        return $countRowAffected;
    }

    /**
     * @param string $sql
     * @param array  $parameters
     * @param bool   $getCountRowsAffected
     *
     * @throws DatabaseException
     *
     * @return int|bool
     */
    public function delete(string $sql, array $parameters = [], bool $getCountRowsAffected = false)
    {
        $statement = $this->prepareBind($sql, $parameters);

        if ($statement === null) {
            return false;
        }

        $startTime = \microtime(true);

        $success = $this->executeStatement($statement);
        if ($success === false) {
            return false;
        }

        $endTime = \microtime(true);

        $this->addQuery($statement, $parameters, $this->getTime($startTime, $endTime));

        $countRowAffected = true;
        if ($getCountRowsAffected) {
            $countRowAffected = (int) $statement->rowCount();
        }

        $statement->closeCursor();
        $statement = null;

        return $countRowAffected;
    }

    /**
     * @param string $sql
     * @param array  $parameters
     *
     * @throws DatabaseException
     *
     * @return int|bool
     */
    public function count(string $sql, array $parameters = [])
    {
        $statement = $this->select($sql, $parameters);

        if ($statement === null) {
            return false;
        }

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
     *
     * @return bool
     */
    public function exec(string $sql, array $parameters = []): bool
    {
        $statement = $this->prepareBind($sql, $parameters);

        if ($statement === null) {
            return false;
        }

        $startTime = \microtime(true);

        $success = $this->executeStatement($statement);

        $endTime = \microtime(true);

        $this->addQuery($statement, $parameters, $this->getTime($startTime, $endTime));

        $statement->closeCursor();
        $statement = null;

        return $success;
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
     * @return array|bool
     */
    public function selectAll(string $sql, array $parameters = [])
    {
        $statement = $this->select($sql, $parameters);

        if ($statement === null) {
            return false;
        }

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
     * @return array|bool
     */
    public function selectRow($sql, array $parameters = [])
    {
        $statement = $this->select($sql, $parameters);

        if ($statement === null) {
            return false;
        }

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
     * @return array|bool
     */
    public function selectCol(string $sql, array $parameters = [])
    {
        $statement = $this->select($sql, $parameters);

        if ($statement === null) {
            return false;
        }

        $datas = $this->readAll($statement);
        $col = [];
        foreach ($datas as $data) {
            $col[] = \current($data);
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
     * @return mixed|bool
     */
    public function selectVar(string $sql, array $parameters = [])
    {
        $var = false;

        $statement = $this->select($sql, $parameters);

        if ($statement === null) {
            return false;
        }

        $row = $this->read($statement);
        if ($row !== false) {
            $var = \current($row);
        }

        $statement->closeCursor();
        $statement = null;

        return $var;
    }

    /**
     * @throws DatabaseException
     * @throws PDOException
     */
    public function startTransaction(): bool
    {
        if ($this->pdo === null) {
            $this->connect();
        }

        return $this->pdo->beginTransaction();
    }

    /**
     * @throws DatabaseException
     */
    public function completeTransaction(): bool
    {
        if ($this->pdo === null) {
            $this->connect();
        }

        if ($this->pdo->inTransaction() === false) {
            return false;
        }

        if ($this->hasErrors()) {
            $this->pdo->rollBack();

            return false;
        }

        $this->pdo->commit();

        return true;
    }

    /**
     * @throws DatabaseException
     */
    public function commitTransaction(): bool
    {
        if ($this->pdo === null) {
            $this->connect();
        }

        if ($this->pdo->inTransaction() === false) {
            return false;
        }

        return $this->pdo->commit();
    }

    /**
     * @throws DatabaseException
     */
    public function rollbackTransaction(): bool
    {
        if ($this->pdo === null) {
            $this->connect();
        }

        if ($this->pdo->inTransaction() === false) {
            return false;
        }

        return $this->pdo->rollBack();
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
        return $this->configurator->hasSaveQueries();
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
     * @param string $table
     *
     * @throws DatabaseException
     *
     * @return bool
     */
    public function truncateTable(string $table): bool
    {
        $sql = 'TRUNCATE TABLE ' . $table;
        if ($this->configurator->getEngine() === 'sqlite') {
            $sql = 'DELETE FROM ' . $table;
        }

        return $this->exec($sql);
    }

    /**
     * @param array $tables
     *
     * @throws DatabaseException
     *
     * @return bool
     */
    public function truncateTables(array $tables): bool
    {
        $success = true;

        foreach ($tables as $table) {
            if ($this->truncateTable($table) === false) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * @param string $table
     *
     * @throws DatabaseException
     *
     * @return bool
     */
    public function dropTable(string $table): bool
    {
        return $this->dropTables([$table]);
    }

    /**
     * @param array $tables
     *
     * @throws DatabaseException
     *
     * @return bool
     */
    public function dropTables(array $tables): bool
    {
        $success = true;

        if ($this->configurator->getEngine() === 'sqlite') {
            foreach ($tables as $table) {
                $sql = 'DROP TABLE IF EXISTS ' . $table;
                if ($this->exec($sql) === false) {
                    $success = false;
                }
            }

            return $success;
        }

        $tables = \implode(',', $tables);

        $sql = 'DROP TABLE IF EXISTS ' . $tables;
        $success = $this->exec($sql);

        return $success;
    }

    /**
     * @param string $filepath
     *
     * @throws DatabaseException
     *
     * @return bool
     */
    public function useSqlFile(string $filepath): bool
    {
        if (!\file_exists($filepath)) {
            throw new DatabaseException('File missing for useSqlFile method: ' . $filepath);
        }

        $sqlFile = \file_get_contents($filepath);

        return $this->exec($sqlFile);
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
