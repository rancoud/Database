<?php

namespace Rancoud\Database;

use Exception;
use PDO;
use PDOStatement;

/**
 * Class Database.
 */
class Database
{
    /**
     * @var Configurator
     */
    protected $configurator = null;

    /**
     * @var PDO
     */
    protected $pdo = null;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var array
     */
    protected $savedQueries = [];

    /**
     * @var Database
     */
    protected static $instance;

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
     * @throws Exception
     *
     * @return Database
     */
    public static function getInstance(Configurator $configurator = null)
    {
        if (self::$instance === null) {
            if ($configurator === null) {
                throw new Exception('Configurator Missing');
            }
            self::$instance = new self($configurator);
        } elseif ($configurator !== null) {
            throw new Exception('Configurator Already Setup');
        }

        return self::$instance;
    }

    /**
     * @throws Exception
     */
    public function connect()
    {
        try {
            $startTime = microtime(true);

            $this->pdo = $this->configurator->createPDOConnection();

            $endTime = microtime(true);

            if ($this->configurator->hasSaveQueries()) {
                $this->savedQueries[] = ['Connection' => $this->getTime($startTime, $endTime)];
            }
        } catch (Exception $e) {
            $this->addErrorConnection($e);

            if ($this->configurator->hasThrowException()) {
                throw new Exception('Error Connecting Database');
            }
        }
    }

    /**
     * @param string $sql
     * @param array  $parameters
     *
     * @throws Exception
     *
     * @return PDOStatement
     */
    protected function prepareBind(string $sql, array $parameters = [])
    {
        if ($this->pdo === null) {
            $this->connect();
        }

        $statement = null;

        try {
            $statement = $this->pdo->prepare($sql);
            if ($statement === false) {
                throw new Exception('Error Prepare Statement');
            }
        } catch (Exception $e) {
            $this->addErrorPrepare($sql, $parameters);
            if ($this->configurator->hasThrowException()) {
                throw new Exception('Error Prepare Statement');
            }
        }

        if ($statement === false) {
            return null;
        }

        foreach ($parameters as $key => $value) {
            $param = $this->getPdoParamType($value);
            if (is_float($value)) {
                $value = (string) $value;
            }

            if ($param === false) {
                throw new Exception('Error Bind Value');
            }

            $statement->bindValue(":$key", $value, $param);
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
        if (is_int($value)) {
            return PDO::PARAM_INT;
        } elseif (is_bool($value)) {
            return PDO::PARAM_BOOL;
        } elseif (null === $value) {
            return PDO::PARAM_NULL;
        } elseif (is_string($value)) {
            return PDO::PARAM_STR;
        } elseif (is_float($value)) {
            return PDO::PARAM_STR;
        } elseif (is_resource($value)) {
            return PDO::PARAM_LOB;
        }

        return false;
    }

    /**
     * @param PDOStatement $statement
     */
    protected function addErrorStatement(PDOStatement $statement)
    {
        $this->errors[] = [
            'query'       => $statement->queryString,
            'query_error' => $statement->errorInfo(),
            'pdo_error'   => $this->pdo->errorInfo(),
            'dump_params' => $this->getDumpParams($statement)
        ];
    }

    /**
     * @param Exception $exception
     */
    protected function addErrorConnection(Exception $exception)
    {
        $this->errors[] = [
            'query'       => $this->configurator->getDsn(),
            'query_error' => null,
            'pdo_error'   => $exception->getMessage(),
            'dump_params' => $this->configurator->getParametersForPDO()
        ];
    }

    /**
     * @param string $sql
     * @param array  $parameters
     */
    protected function addErrorPrepare(string $sql, array $parameters)
    {
        $this->errors[] = [
            'query'       => $sql,
            'query_error' => null,
            'pdo_error'   => $this->pdo->errorInfo(),
            'dump_params' => $parameters
        ];
    }

    /**
     * @param PDOStatement $statement
     * @param array        $parameters
     * @param float        $time
     */
    protected function addQuery(PDOStatement $statement, array $parameters, float $time)
    {
        if ($this->configurator->hasSaveQueries()) {
            $this->savedQueries[] = [
                'query'      => $statement->queryString,
                'parameters' => $parameters,
                'time'       => $time
            ];
        }
    }

    /**
     * @param PDOStatement $statement
     *
     * @return string
     */
    protected function getDumpParams(PDOStatement $statement)
    {
        ob_start();
        $statement->debugDumpParams();
        $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }

    /**
     * @param string $sql
     * @param array  $parameters
     *
     * @throws Exception
     *
     * @return PDOStatement|null
     */
    public function select(string $sql, array $parameters = [])
    {
        $statement = $this->prepareBind($sql, $parameters);

        if ($statement === null) {
            return null;
        }

        $startTime = microtime(true);

        $this->executeStatement($statement);

        $endTime = microtime(true);

        $this->addQuery($statement, $parameters, $this->getTime($startTime, $endTime));

        return $statement;
    }

    /**
     * @param PDOStatement $statement
     *
     * @throws Exception
     *
     * @return bool
     */
    protected function executeStatement(PDOStatement $statement)
    {
        $success = false;

        try {
            $success = $statement->execute();
            if ($success === false) {
                throw new Exception('Error Execute');
            }
        } catch (Exception $e) {
            $this->addErrorStatement($statement);
            if ($this->configurator->hasThrowException()) {
                throw new Exception('Error Execute');
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
    public function readAll(PDOStatement $statement, $fetchType = PDO::FETCH_ASSOC)
    {
        return $statement->fetchAll($fetchType);
    }

    /**
     * @param string $sql
     * @param array  $parameters
     * @param bool   $getLastInsertId
     *
     * @throws Exception
     *
     * @return int|bool
     */
    public function insert(string $sql, array $parameters = [], bool $getLastInsertId = false)
    {
        $statement = $this->prepareBind($sql, $parameters);

        if ($statement === null) {
            return false;
        }

        $startTime = microtime(true);

        $success = $this->executeStatement($statement);
        if ($success === false) {
            return false;
        }

        $endTime = microtime(true);

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
     * @throws Exception
     *
     * @return int|bool
     */
    public function update(string $sql, array $parameters = [], bool $getCountRowsAffected = false)
    {
        $statement = $this->prepareBind($sql, $parameters);

        if ($statement === null) {
            return false;
        }

        $startTime = microtime(true);

        $success = $this->executeStatement($statement);
        if ($success === false) {
            return false;
        }

        $endTime = microtime(true);

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
     * @throws Exception
     *
     * @return int|bool
     */
    public function delete(string $sql, array $parameters = [], bool $getCountRowsAffected = false)
    {
        $statement = $this->prepareBind($sql, $parameters);

        if ($statement === null) {
            return false;
        }

        $startTime = microtime(true);

        $success = $this->executeStatement($statement);
        if ($success === false) {
            return false;
        }

        $endTime = microtime(true);

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
     * @throws Exception
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

        $count = (int) current($cursor);

        $statement->closeCursor();
        $statement = null;

        return $count;
    }

    /**
     * @param string $sql
     * @param array  $parameters
     *
     * @throws Exception
     *
     * @return bool
     */
    public function exec(string $sql, array $parameters = [])
    {
        $statement = $this->prepareBind($sql, $parameters);

        if ($statement === null) {
            return false;
        }

        $startTime = microtime(true);

        $success = $this->executeStatement($statement);

        $endTime = microtime(true);

        $this->addQuery($statement, $parameters, $this->getTime($startTime, $endTime));

        $statement->closeCursor();
        $statement = null;

        return $success;
    }

    /**
     * @return PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * @param string $sql
     * @param array  $parameters
     *
     * @throws Exception
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
     * @throws Exception
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
     * @throws Exception
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
            $col[] = current($data);
        }

        $statement->closeCursor();
        $statement = null;

        return $col;
    }

    /**
     * @param string $sql
     * @param array  $parameters
     *
     * @throws Exception
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
            $var = current($row);
        }

        $statement->closeCursor();
        $statement = null;

        return $var;
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function startTransaction()
    {
        if ($this->pdo === null) {
            $this->connect();
        }

        return $this->pdo->beginTransaction();
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function completeTransaction()
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
     * @throws Exception
     *
     * @return bool
     */
    public function commitTransaction()
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
     * @throws Exception
     *
     * @return bool
     */
    public function rollbackTransaction()
    {
        if ($this->pdo === null) {
            $this->connect();
        }

        if ($this->pdo->inTransaction() === false) {
            return false;
        }

        return $this->pdo->rollBack();
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return array|null
     */
    public function getLastError()
    {
        $countErrors = count($this->errors);
        if ($countErrors === 0) {
            return null;
        }

        return $this->errors[$countErrors - 1];
    }

    public function cleanErrors()
    {
        $this->errors = [];
    }

    /**
     * @return bool
     */
    public function hasSaveQueries()
    {
        return $this->configurator->hasSaveQueries();
    }

    public function enableSaveQueries()
    {
        $this->configurator->enableSaveQueries();
    }

    public function disableSaveQueries()
    {
        $this->configurator->disableSaveQueries();
    }

    public function cleanSavedQueries()
    {
        $this->savedQueries = [];
    }

    /**
     * @return array
     */
    public function getSavedQueries()
    {
        return $this->savedQueries;
    }

    /**
     * @param string $table
     *
     * @throws Exception
     *
     * @return bool
     */
    public function truncateTable(string $table)
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
     * @throws Exception
     *
     * @return bool
     */
    public function truncateTables(array $tables)
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
     * @throws Exception
     *
     * @return bool
     */
    public function dropTable(string $table)
    {
        return $this->dropTables([$table]);
    }

    /**
     * @param array $tables
     *
     * @throws Exception
     *
     * @return bool
     */
    public function dropTables(array $tables)
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

        $tables = implode(',', $tables);

        $sql = 'DROP TABLE IF EXISTS ' . $tables;
        $success = $this->exec($sql);

        return $success;
    }

    /**
     * @param string $filepath
     *
     * @throws Exception
     *
     * @return bool
     */
    public function useSqlFile(string $filepath)
    {
        if (!file_exists($filepath)) {
            throw new Exception('File missing for useSqlFile method: ' . $filepath);
        }

        $sqlFile = file_get_contents($filepath);

        return $this->exec($sqlFile);
    }

    /**
     * @param float $startTime
     * @param float $endTime
     *
     * @return float|int
     */
    protected function getTime(float $startTime, float $endTime)
    {
        return round(($endTime - $startTime) * 1000000) / 1000000;
    }

    public function disconnect()
    {
        $this->pdo = null;
    }
}
