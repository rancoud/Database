<?php

namespace Rancoud\Database;

use Exception;

/**
 * Class Database.
 */
class Database
{
    /** @var Driver */
    protected $driver;

    /**
     * Database constructor.
     *
     * @param Configurator $configurator
     * @param Driver       $driver
     */
    public function __construct(Configurator $configurator, Driver $driver)
    {
        $this->driver = $driver;
        /* @noinspection PhpUndefinedMethodInspection */
        $this->driver->setConfigurator($configurator);
    }

    /**
     * @return Driver
     */
    public function getDriver()
    {
        return $this->driver->getDriver();
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (!method_exists($this->driver, $name)) {
            throw new Exception('Method Invalid! : ' . $name);
        }

        return call_user_func_array([$this->driver, $name], $arguments);
    }
}
