<?php

declare(strict_types=1);

namespace Rancoud\Database;

use PDO;
use PDOException;

/**
 * Class Configurator.
 */
class Configurator
{
    /** @var string */
    protected $engine;

    /** @var string */
    protected $host;

    /** @var string */
    protected $user;

    /** @var string */
    protected $password;

    /** @var string */
    protected $database;

    /** @var array */
    protected $parameters = [];

    /** @var bool */
    protected $saveQueries = false;

    /** @var bool */
    protected $permanentConnection = false;

    /** @var string */
    protected $reportError = 'exception';

    /** @var string */
    protected $charset = 'utf8';

    /** @var string[] */
    protected $keySettings = [
        'engine',
        'host',
        'user',
        'password',
        'database',
        'save_queries',
        'permanent_connection',
        'report_error',
        'charset',
        'parameters'
    ];

    /**
     * DatabaseConfigurator constructor.
     *
     * @param array $settings
     *
     * @throws DatabaseException
     */
    public function __construct(array $settings)
    {
        $this->verifySettings($settings);

        $this->setMandatorySettings($settings);

        $this->setOptionnalsParameters($settings);
    }

    /**
     * @param array $settings
     *
     * @throws DatabaseException
     */
    protected function verifySettings(array $settings): void
    {
        foreach ($settings as $key => $value) {
            if (!in_array($key, $this->keySettings, true)) {
                throw new DatabaseException('"' . $key . '" settings is not recognized');
            }
        }
    }

    /**
     * @param array $settings
     *
     * @throws DatabaseException
     */
    protected function setMandatorySettings(array $settings): void
    {
        $props = ['engine', 'host', 'user', 'password', 'database'];
        foreach ($props as $prop) {
            if (!isset($settings[$prop]) || !is_string($settings[$prop])) {
                throw new DatabaseException('"' . $prop . '" settings is not defined or not a string');
            }

            $this->{'set' . ucfirst($prop)}($settings[$prop]);
        }
    }

    /**
     * @param array $settings
     *
     * @throws DatabaseException
     */
    protected function setOptionnalsParameters(array $settings): void
    {
        if (array_key_exists('save_queries', $settings)) {
            $this->saveQueries = (bool) $settings['save_queries'];
        }

        if (array_key_exists('permanent_connection', $settings)) {
            $this->permanentConnection = (bool) $settings['permanent_connection'];
        }

        if (array_key_exists('report_error', $settings)) {
            $this->setReportError($settings['report_error']);
        }

        if (array_key_exists('charset', $settings)) {
            $this->setCharset($settings['charset']);
        }

        if (array_key_exists('parameters', $settings)) {
            $this->setParameters($settings['parameters']);
        }
    }

    /**
     * @return string
     */
    public function getEngine(): string
    {
        return $this->engine;
    }

    /**
     * @param string $engine
     *
     * @throws DatabaseException
     */
    public function setEngine(string $engine): void
    {
        $enginesAvailables = PDO::getAvailableDrivers();
        if (!in_array($engine, $enginesAvailables, true)) {
            throw new DatabaseException('The engine "' . $engine . '" is not available for PDO');
        }

        $this->engine = $engine;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @param string $user
     */
    public function setUser(string $user): void
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getDatabase(): string
    {
        return $this->database;
    }

    /**
     * @param string $database
     */
    public function setDatabase(string $database): void
    {
        $this->database = $database;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param mixed $key
     * @param mixed $value
     */
    public function setParameter($key, $value): void
    {
        $this->parameters[$key] = $value;
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * @return array
     */
    public function getParametersForPDO(): array
    {
        $parameters = $this->getParameters();

        if ($this->getEngine() === 'mysql') {
            $parameters[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES ' . $this->getCharset();
        }

        if ($this->getReportError() === 'silent') {
            $parameters[PDO::ATTR_ERRMODE] = PDO::ERRMODE_SILENT;
        } elseif ($this->getReportError() === 'exception') {
            $parameters[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        }

        $parameters[PDO::ATTR_PERSISTENT] = $this->permanentConnection;

        return $parameters;
    }

    /**
     * @return bool
     */
    public function hasSaveQueries(): bool
    {
        return $this->saveQueries;
    }

    public function enableSaveQueries(): void
    {
        $this->saveQueries = true;
    }

    public function disableSaveQueries(): void
    {
        $this->saveQueries = false;
    }

    /**
     * @return bool
     */
    public function hasPermanentConnection(): bool
    {
        return $this->permanentConnection;
    }

    public function enablePermanentConnection(): void
    {
        $this->permanentConnection = true;
    }

    public function disablePermanentConnection(): void
    {
        $this->permanentConnection = false;
    }

    /**
     * @return string
     */
    public function getReportError(): string
    {
        return $this->reportError;
    }

    /**
     * @param string $reportError
     *
     * @throws DatabaseException
     */
    public function setReportError(string $reportError): void
    {
        if (!in_array($reportError, ['silent', 'exception'], true)) {
            throw new DatabaseException('The report error "' . $reportError . '" is incorrect. (silent , exception)');
        }

        $this->reportError = $reportError;
    }

    /**
     * @return bool
     */
    public function hasThrowException(): bool
    {
        return $this->reportError === 'exception';
    }

    /**
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * @param string $charset
     */
    public function setCharset(string $charset): void
    {
        $this->charset = $charset;
    }

    /**
     * @return string
     */
    public function getDsn(): string
    {
        $engine = $this->getEngine();
        $host = $this->getHost();
        $database = $this->getDatabase();

        $dsn = $engine . ':host=' . $host . ';dbname=' . $database;
        if ($engine === 'sqlite') {
            $dsn = 'sqlite:' . $database;
        }

        return $dsn;
    }

    /**
     * @throws DatabaseException
     *
     * @return PDO
     */
    public function createPDOConnection(): PDO
    {
        $user = $this->getUser();
        $password = $this->getPassword();
        $parameters = $this->getParametersForPDO();
        $dsn = $this->getDsn();

        try {
            if ($this->getEngine() !== 'sqlite') {
                return new PDO($dsn, $user, $password, $parameters);
            }

            return new PDO($dsn, null, null, $parameters);
        } catch (PDOException $e) {
            throw new DatabaseException($e->getMessage());
        }
    }
}
