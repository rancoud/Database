<?php

namespace Rancoud\Database;

use Exception;

/**
 * Class DatabaseConfigurator.
 */
class DatabaseConfigurator implements Configurator
{
    protected $engine;

    protected $host;

    protected $user;

    protected $password;

    protected $database;

    protected $parameters;

    protected $saveQueries = false;

    /**
     * DatabaseConfigurator constructor.
     *
     * @param array $settings
     *
     * @throws \Exception
     */
    public function __construct(array $settings)
    {
        $props = ['engine', 'host', 'user', 'password', 'database'];
        foreach ($props as $prop) {
            if (!isset($settings[$prop]) || !is_string($settings[$prop])) {
                throw new Exception('"' . $prop . '" settings is not defined or not a string', 10);
            }

            $this->{$prop} = $settings[$prop];
        }

        if (!isset($settings['parameters'])) {
            $settings['parameters'] = [];
        }

        if (array_key_exists('save_queries', $settings)) {
            $this->saveQueries = (bool) $settings['save_queries'];
        }

        if (!is_array($settings['parameters'])) {
            throw new Exception('"parameters" settings is not an array: ' . gettype($settings['parameters']), 20);
        }

        $this->parameters = $settings['parameters'];
    }

    /**
     * @return mixed
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return mixed
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return bool
     */
    public function hasSaveQueries()
    {
        return $this->saveQueries;
    }
}
