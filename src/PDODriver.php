<?php

namespace Rancoud\Database;

use Exception;
use PDO;
use PDOException;
use PDOStatement;

/**
 * Class PDODriver.
 */
class PDODriver extends DatabaseDriver
{
    /**
     * @var PDO
     */
    private $pdo = null;

    private $errors = [];

    private $configurator = null;

    private $savedQueries = [];

    private $saveQueries = false;

    private static $instance;

    /**
     * @param DatabaseConfigurator|null $configurator
     *
     * @return PDODriver
     */
    public static function getInstance(DatabaseConfigurator $configurator = null)
    {
        if (null === self::$instance) {
            self::$instance = new self($configurator);
        }

        return self::$instance;
    }

    /**
     * PDODriver constructor.
     *
     * @param DatabaseConfigurator|null $configurator
     */
    public function __construct(DatabaseConfigurator $configurator = null)
    {
        if ($configurator !== null) {
            $this->configurator = $configurator;
        }
    }

    /**
     * @param DatabaseConfigurator $configurator
     */
    public function setConfigurator(DatabaseConfigurator $configurator)
    {
        $this->configurator = $configurator;
    }

    /**
     * @param Configurator $configurator
     *
     * @throws \Exception
     */
    public function configureDriver(Configurator $configurator)
    {
        $engine = $configurator->getEngine();
        $host = $configurator->getHost();
        $database = $configurator->getDatabase();
        $user = $configurator->getUser();
        $password = $configurator->getPassword();
        $parameters = $configurator->getParameters();
        if ($configurator->hasSaveQueries() === true) {
            $this->enableSaveQueries();
        }

        if (empty($parameters)) {
            $parameters = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
            ];
        } elseif (is_array($parameters) && !isset($parameters[PDO::ATTR_ERRMODE])) {
            $parameters[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        } elseif (is_array($parameters) && !isset($parameters[PDO::ATTR_ERRMODE])) {
            $parameters[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        }

        try {
            $startTime = microtime(true);

            $dsn = $engine . ':host=' . $host . ';dbname=' . $database;
            $this->pdo = new PDO($dsn, $user, $password, $parameters);

            $endTime = microtime(true);
            $this->savedQueries[] = ['Connection' => $this->getTime($startTime, $endTime)];
        } catch (PDOException $e) {
            throw new Exception('Error Connecting Database', 10);
        }
    }

    /**
     * @param       $sql
     * @param array $parameters
     *
     * @throws \Exception
     *
     * @return \PDOStatement
     */
    private function prepareBind($sql, $parameters = [])
    {
        if ($this->pdo === null) {
            $this->configureDriver($this->configurator);
        }
        $statement = $this->pdo->prepare($sql);
        if ($statement === false) {
            throw new Exception('Error Prepare Statement', 20);
        }

        foreach ($parameters as $key => $value) {
            $param = $this->getPdoParamType($value);
            if (is_float($value)) {
                $value = (string) $value;
            }

            if ($param !== false) {
                $statement->bindValue(":$key", $value, $param);
            }
        }

        return $statement;
    }

    /**
     * @param $value
     *
     * @return bool|int
     */
    private function getPdoParamType($value)
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
     * @param \PDOStatement $statement
     */
    private function saveErrors(PDOStatement $statement)
    {
        $this->errors[] = [
            'query'       => $statement->queryString,
            'query_error' => $statement->errorInfo(),
            'pdo_error'   => $this->pdo->errorInfo(),
            'dump_params' => $this->getDumpParams($statement)
        ];
    }

    /**
     * @param \PDOStatement $statement
     * @param array         $parameters
     * @param               $time
     */
    private function saveQueries(PDOStatement $statement, $parameters, $time)
    {
        if ($this->saveQueries) {
            $this->savedQueries[] = [
                'query'      => $statement->queryString,
                'parameters' => $parameters,
                'time'       => $time
            ];
        }
    }

    /**
     * @param \PDOStatement $statement
     *
     * @return string
     */
    private function getDumpParams(PDOStatement $statement)
    {
        ob_start();
        $statement->debugDumpParams();
        $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }

    /**
     * @param       $sql
     * @param array $parameters
     *
     * @throws \Exception
     *
     * @return \PDOStatement
     */
    public function select($sql, $parameters = [])
    {
        $statement = $this->prepareBind($sql, $parameters);

        $startTime = microtime(true);

        $results = $statement->execute();
        if ($results === false) {
            $this->saveErrors($statement);
            throw new Exception('Error Execute Select Query', 30);
        }

        $endTime = microtime(true);

        $this->saveQueries($statement, $parameters, $this->getTime($startTime, $endTime));

        return $statement;
    }

    /**
     * @param PDOStatement $statement
     * @param null         $fetchType
     *
     * @return mixed
     */
    public function read($statement, $fetchType = null)
    {
        if ($fetchType === null) {
            $fetchType = PDO::FETCH_ASSOC;
        }

        return $statement->fetch($fetchType);
    }

    /**
     * @param \PDOStatement $statement
     * @param null          $fetchType
     *
     * @return array
     */
    public function readAll(PDOStatement $statement, $fetchType = null)
    {
        if ($fetchType === null) {
            $fetchType = PDO::FETCH_ASSOC;
        }

        return $statement->fetchAll($fetchType);
    }

    /**
     * @param       $sql
     * @param array $parameters
     * @param bool  $getLastInsertId
     *
     * @throws \Exception
     *
     * @return int|null
     */
    public function insert($sql, $parameters = [], $getLastInsertId = false)
    {
        $statement = $this->prepareBind($sql, $parameters);

        $startTime = microtime(true);

        $results = $statement->execute();
        if ($results === false) {
            $this->saveErrors($statement);
            throw new Exception('Error Execute Insert Query', 40);
        }

        $endTime = microtime(true);

        $this->saveQueries($statement, $parameters, $this->getTime($startTime, $endTime));
        if ($getLastInsertId === true) {
            return (int) $this->pdo->lastInsertId();
        }

        return null;
    }

    /**
     * @param       $sql
     * @param array $parameters
     * @param bool  $getCountRowAffected
     *
     * @throws \Exception
     *
     * @return int|null
     */
    public function update($sql, $parameters = [], $getCountRowAffected = false)
    {
        $statement = $this->prepareBind($sql, $parameters);

        $startTime = microtime(true);

        $results = $statement->execute();
        if ($results === false) {
            $this->saveErrors($statement);
            throw new Exception('Error Execute Update Query', 50);
        }

        $endTime = microtime(true);

        $this->saveQueries($statement, $parameters, $this->getTime($startTime, $endTime));
        if ($getCountRowAffected) {
            return (int) $statement->rowCount();
        }

        return null;
    }

    /**
     * @param       $sql
     * @param array $parameters
     * @param bool  $getCountRowAffected
     *
     * @throws \Exception
     *
     * @return int|null
     */
    public function delete($sql, $parameters = [], $getCountRowAffected = false)
    {
        $statement = $this->prepareBind($sql, $parameters);

        $startTime = microtime(true);

        $results = $statement->execute();
        if ($results === false) {
            $this->saveErrors($statement);
            throw new Exception('Error Execute Delete Query', 60);
        }

        $endTime = microtime(true);

        $this->saveQueries($statement, $parameters, $this->getTime($startTime, $endTime));
        if ($getCountRowAffected) {
            return (int) $statement->rowCount();
        }

        return null;
    }

    /**
     * @param       $sql
     * @param array $parameters
     *
     * @return int
     */
    public function count($sql, $parameters = [])
    {
        $statement = $this->select($sql, $parameters);
        $cursor = $statement->fetch(PDO::FETCH_ASSOC);

        return (int) current($cursor);
    }

    /**
     * @param       $sql
     * @param array $parameters
     *
     * @throws \Exception
     */
    public function exec($sql, $parameters = [])
    {
        $statement = $this->prepareBind($sql, $parameters);

        $startTime = microtime(true);

        $results = $statement->execute();
        if ($results === false) {
            $this->saveErrors($statement);
            throw new Exception('Error Execute Exec Query', 70);
        }

        $endTime = microtime(true);

        $this->saveQueries($statement, $parameters, $this->getTime($startTime, $endTime));
    }

    /**
     * @return PDO
     */
    public function getDriver()
    {
        return $this->pdo;
    }

    /**
     * @param       $sql
     * @param array $parameters
     *
     * @return array
     */
    public function selectAll($sql, $parameters = [])
    {
        $cursor = $this->select($sql, $parameters);

        return $this->readAll($cursor);
    }

    /**
     * @param       $sql
     * @param array $parameters
     *
     * @return mixed
     */
    public function selectRow($sql, $parameters = [])
    {
        $cursor = $this->select($sql, $parameters);
        $row = $this->read($cursor);

        return $row;
    }

    /**
     * @param       $sql
     * @param array $parameters
     *
     * @return array
     */
    public function selectCol($sql, $parameters = [])
    {
        $cursor = $this->select($sql, $parameters);
        $datas = $this->readAll($cursor);
        $col = [];
        foreach ($datas as $data) {
            $col[] = current($data);
        }

        return $col;
    }

    /**
     * @param       $sql
     * @param array $parameters
     *
     * @return mixed|null
     */
    public function selectVar($sql, $parameters = [])
    {
        $cursor = $this->select($sql, $parameters);
        $row = $this->read($cursor);
        if ($row === false) {
            return null;
        }

        return current($row);
    }

    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    public function commit()
    {
        $this->pdo->commit();
    }

    public function rollback()
    {
        $this->pdo->rollBack();
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return empty($this->errors);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    public function cleanErrors()
    {
        $this->errors = [];
    }

    public function enableSaveQueries()
    {
        $this->saveQueries = true;
    }

    public function diableSaveQueries()
    {
        $this->saveQueries = false;
    }

    public function cleanSavedQueries()
    {
        $this->saveQueries = [];
    }

    /**
     * @return array
     */
    public function getSavedQueries()
    {
        return $this->savedQueries;
    }

    /**
     * @param $string
     *
     * @return mixed
     */
    private function cleanField($string)
    {
        return str_replace('`', '', $string);
    }

    /**
     * @param $table
     */
    public function truncate($table)
    {
        $table = $this->cleanField($table);
        $sql = 'TRUNCATE TABLE `' . $table . '`';
        $this->exec($sql);
    }

    /**
     * @param $tables
     */
    public function dropTable($tables)
    {
        if (is_array($tables)) {
            $tables = array_map([$this, 'cleanField'], $tables);
            $tables = implode('`,`', $tables);
        } else {
            $tables = $this->cleanField($tables);
        }
        $sql = 'DROP TABLE IF EXISTS `' . $tables . '`';
        $this->exec($sql);
    }

    /**
     * @param $tables
     */
    public function optimize($tables)
    {
        if (is_array($tables[0])) {
            $tables = array_map([$this, 'cleanField'], $tables);
            $tables = implode('`,`', $tables);
        } else {
            $tables = $this->cleanField($tables);
        }
        $sql = 'OPTIMIZE TABLE `' . $tables . '`';
        $this->exec($sql);
    }

    /**
     * @param $filepath
     *
     * @throws \Exception
     */
    public function useSqlFile($filepath)
    {
        if (!file_exists($filepath)) {
            throw new Exception('File missing for useSqlFile method: ' . $filepath, 80);
        }

        $sqlFile = file_get_contents($filepath);

        $this->exec($sqlFile);
    }

    /**
     * @param $startTime
     * @param $endTime
     *
     * @return float|int
     */
    protected function getTime($startTime, $endTime)
    {
        return round(($endTime - $startTime) * 1000000) / 1000000;
    }
}
