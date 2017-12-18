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
                throw new Exception('Configurator Missing', 10);
            }
            self::$instance = new self($configurator);
        } elseif ($configurator !== null) {
            throw new Exception('Configurator Already Setup', 11);
        }

        return self::$instance;
    }

    /**
     * @throws Exception
     */
    public function connnect()
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
                throw new Exception('Error Connecting Database', 20);
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
            $this->connnect();
        }

        $statement = null;

        try {
            $statement = $this->pdo->prepare($sql);
            if ($statement === false) {
                throw new Exception('Error Prepare Statement', 30);
            }
        } catch (Exception $e) {
            $this->addErrorPrepare($sql, $parameters);
            if ($this->configurator->hasThrowException()) {
                throw new Exception('Error Prepare Statement', 30);
            }
        }

        foreach ($parameters as $key => $value) {
            $param = $this->getPdoParamType($value);
            if (is_float($value)) {
                $value = (string) $value;
            }

            if ($param !== false) {
                try {
                    $success = $statement->bindValue(":$key", $value, $param);
                    if ($success === false) {
                        throw new Exception('Error Bind Value', 31);
                    }
                } catch (Exception $e) {
                    $this->addErrorStatement($statement);
                    if ($this->configurator->hasThrowException()) {
                        throw new Exception('Error Bind Value', 31);
                    }
                }
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
     * @return PDOStatement
     */
    public function select(string $sql, array $parameters = [])
    {
        $statement = $this->prepareBind($sql, $parameters);

        $startTime = microtime(true);

        try {
            $results = $statement->execute();
            if ($results === false) {
                throw new Exception('Error Execute Select Query', 40);
            }
        } catch (Exception $e) {
            $this->addErrorStatement($statement);
            if ($this->configurator->hasThrowException()) {
                throw new Exception('Error Execute Select Query', 40);
            }
        }

        $endTime = microtime(true);

        $this->addQuery($statement, $parameters, $this->getTime($startTime, $endTime));

        return $statement;
    }

    /**
     * @param PDOStatement $statement
     * @param int          $fetchType
     *
     * @return mixed
     */
    protected function read(PDOStatement $statement, $fetchType = PDO::FETCH_ASSOC)
    {
        return $statement->fetch($fetchType);
    }

    /**
     * @param PDOStatement $statement
     * @param int          $fetchType
     *
     * @return array
     */
    protected function readAll(PDOStatement $statement, $fetchType = PDO::FETCH_ASSOC)
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
     * @return int|null
     */
    public function insert(string $sql, array $parameters = [], bool $getLastInsertId = false)
    {
        $statement = $this->prepareBind($sql, $parameters);

        $startTime = microtime(true);

        try {
            $results = $statement->execute();
            if ($results === false) {
                throw new Exception('Error Execute Insert Query', 50);
            }
        } catch (Exception $e) {
            $this->addErrorStatement($statement);
            if ($this->configurator->hasThrowException()) {
                throw new Exception('Error Execute Insert Query', 50);
            }
        }

        $endTime = microtime(true);

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
     * @param bool   $getCountRowAffected
     *
     * @throws Exception
     *
     * @return int|null
     */
    public function update(string $sql, array $parameters = [], bool $getCountRowAffected = false)
    {
        $statement = $this->prepareBind($sql, $parameters);

        $startTime = microtime(true);

        try {
            $results = $statement->execute();
            if ($results === false) {
                throw new Exception('Error Execute Update Query', 60);
            }
        } catch (Exception $e) {
            $this->addErrorStatement($statement);
            if ($this->configurator->hasThrowException()) {
                throw new Exception('Error Execute Update Query', 60);
            }
        }

        $endTime = microtime(true);

        $this->addQuery($statement, $parameters, $this->getTime($startTime, $endTime));

        $countRowAffected = null;
        if ($getCountRowAffected) {
            $countRowAffected = (int) $statement->rowCount();
        }

        $statement->closeCursor();
        $statement = null;

        return $countRowAffected;
    }

    /**
     * @param string $sql
     * @param array  $parameters
     * @param bool   $getCountRowAffected
     *
     * @throws Exception
     *
     * @return int|null
     */
    public function delete(string $sql, array $parameters = [], bool $getCountRowAffected = false)
    {
        $statement = $this->prepareBind($sql, $parameters);

        $startTime = microtime(true);

        try {
            $results = $statement->execute();
            if ($results === false) {
                throw new Exception('Error Execute Delete Query', 70);
            }
        } catch (Exception $e) {
            $this->addErrorStatement($statement);
            if ($this->configurator->hasThrowException()) {
                throw new Exception('Error Execute Delete Query', 70);
            }
        }

        $endTime = microtime(true);

        $this->addQuery($statement, $parameters, $this->getTime($startTime, $endTime));

        $countRowAffected = null;
        if ($getCountRowAffected) {
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
     * @return int
     */
    public function count(string $sql, array $parameters = [])
    {
        $statement = $this->select($sql, $parameters);
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
     */
    public function exec(string $sql, array $parameters = [])
    {
        $statement = $this->prepareBind($sql, $parameters);

        $startTime = microtime(true);

        try {
            $results = $statement->execute();
            if ($results === false) {
                throw new Exception('Error Execute Exec Query', 80);
            }
        } catch (Exception $e) {
            $this->addErrorStatement($statement);
            if ($this->configurator->hasThrowException()) {
                throw new Exception('Error Execute Exec Query', 80);
            }
        }

        $endTime = microtime(true);

        $this->addQuery($statement, $parameters, $this->getTime($startTime, $endTime));

        $statement->closeCursor();
        $statement = null;
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
     * @return array
     */
    public function selectAll(string $sql, array $parameters = [])
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
     * @throws Exception
     *
     * @return mixed
     */
    public function selectRow($sql, array $parameters = [])
    {
        $statement = $this->select($sql, $parameters);

        $row = $this->read($statement);

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
     * @return array
     */
    public function selectCol(string $sql, array $parameters = [])
    {
        $statement = $this->select($sql, $parameters);
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
     * @return mixed|null
     */
    public function selectVar(string $sql, array $parameters = [])
    {
        $var = null;

        $statement = $this->select($sql, $parameters);
        $row = $this->read($statement);
        if ($row !== false) {
            $var = current($row);
        }

        $statement->closeCursor();
        $statement = null;

        return $var;
    }

    protected function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    protected function commit()
    {
        $this->pdo->commit();
    }

    protected function rollback()
    {
        $this->pdo->rollBack();
    }

    public function startTransaction()
    {
        $this->beginTransaction();
    }

    public function completeTransaction()
    {
        if ($this->hasErrors()) {
            $this->rollback();
        } else {
            $this->commit();
        }
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
     * @param string $string
     *
     * @return string
     */
    protected function cleanField(string $string)
    {
        return str_replace('`', '', $string);
    }

    /**
     * @param string $table
     *
     * @throws Exception
     */
    public function truncate(string $table)
    {
        $table = $this->cleanField($table);
        $sql = 'TRUNCATE TABLE `' . $table . '`';
        $this->exec($sql);
    }

    /**
     * @param string $table
     *
     * @throws Exception
     */
    public function dropTable(string $table)
    {
        $this->dropTables([$table]);
    }

    /**
     * @param string[] $tables
     *
     * @throws Exception
     */
    public function dropTables(array $tables)
    {
        $tables = array_map([$this, 'cleanField'], $tables);
        $tables = implode('`,`', $tables);

        $sql = 'DROP TABLE IF EXISTS `' . $tables . '`';
        $this->exec($sql);
    }

    /**
     * @param string $table
     *
     * @throws Exception
     */
    public function optimizeTable(string $table)
    {
        $this->optimizeTables([$table]);
    }

    /**
     * @param string[] $tables
     *
     * @throws Exception
     */
    public function optimizeTables(array $tables)
    {
        $tables = array_map([$this, 'cleanField'], $tables);
        $tables = implode('`,`', $tables);

        $sql = 'OPTIMIZE TABLE `' . $tables . '`';
        $this->exec($sql);
    }

    /**
     * @param string $filepath
     *
     * @throws Exception
     */
    public function useSqlFile(string $filepath)
    {
        if (!file_exists($filepath)) {
            throw new Exception('File missing for useSqlFile method: ' . $filepath, 80);
        }

        $sqlFile = file_get_contents($filepath);

        $this->exec($sqlFile);
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
