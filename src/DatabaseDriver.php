<?php

namespace Rancoud\Database;

/**
 * Class DatabaseDriver.
 */
class DatabaseDriver extends Driver
{
    /**
     * @param Configurator $configurator
     */
    public function configureDriver(Configurator $configurator)
    {
    }

    /**
     * Empty Function.
     */
    public function getDriver()
    {
    }

    /**
     * @param $sql
     * @param $parameters
     */
    public function select($sql, $parameters)
    {
    }

    /**
     * @param $sql
     * @param $parameters
     */
    public function read($sql, $parameters)
    {
    }

    /**
     * @param $sql
     * @param $parameters
     */
    public function insert($sql, $parameters)
    {
    }

    /**
     * @param $sql
     * @param $parameters
     */
    public function update($sql, $parameters)
    {
    }

    /**
     * @param $sql
     * @param $parameters
     */
    public function delete($sql, $parameters)
    {
    }

    /**
     * @param $sql
     * @param $parameters
     */
    public function count($sql, $parameters)
    {
    }

    /**
     * @param $sql
     * @param $parameters
     */
    public function exec($sql, $parameters)
    {
    }

    /**
     * @param $sql
     * @param $parameters
     */
    public function selectAll($sql, $parameters)
    {
    }

    /**
     * @param $sql
     * @param $parameters
     */
    public function selectRow($sql, $parameters)
    {
    }

    /**
     * @param $sql
     * @param $parameters
     */
    public function selectCol($sql, $parameters)
    {
    }

    /**
     * @param $sql
     * @param $parameters
     */
    public function selectVar($sql, $parameters)
    {
    }

    public function beginTransaction()
    {
    }

    public function commit()
    {
    }

    public function rollback()
    {
    }

    public function hasError()
    {
    }

    public function getError()
    {
    }
}
