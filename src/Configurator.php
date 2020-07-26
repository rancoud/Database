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
    protected string $driver;

    /** @var string */
    protected string $host;

    /** @var string */
    protected string $user;

    /** @var string */
    protected string $password;

    /** @var string */
    protected string $database;

    /** @var array */
    protected array $parameters = [];

    /** @var bool */
    protected bool $saveQueries = false;

    /** @var bool */
    protected bool $persistentConnection = false;

    /** @var array */
    protected static array $defaultCharsetByDriver = [
        'mysql' => 'utf8mb4',
        'pgsql' => 'UTF8'
    ];

    /** @var string|null */
    protected ?string $charset = null;

    /** @var string[] */
    protected static array $mandatorySettings = [
        'driver',
        'host',
        'user',
        'password',
        'database'
    ];

    /** @var string[] */
    protected static array $keySettings = [
        'driver',
        'host',
        'user',
        'password',
        'database',
        'save_queries',
        'persistent_connection',
        'charset',
        'parameters'
    ];

    /** @var array */
    protected static array $dsnFormats = [
        'sqlite' => '%1$s:%3$s'
    ];

    /** @var string */
    protected static string $defaultDSN = '%1$s:host=%2$s;dbname=%3$s';

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

        $this->setOptionalsParameters($settings);
    }

    /**
     * @param array $settings
     *
     * @throws DatabaseException
     */
    protected function verifySettings(array $settings): void
    {
        $keys = \array_keys($settings);
        $wrongSettings = \array_diff($keys, static::$keySettings);
        if (!empty($wrongSettings)) {
            $key = \reset($wrongSettings);
            throw new DatabaseException('"' . $key . '" settings is not recognized');
        }
    }

    /**
     * @param array $settings
     *
     * @throws DatabaseException
     */
    protected function setMandatorySettings(array $settings): void
    {
        foreach (static::$mandatorySettings as $prop) {
            if (!isset($settings[$prop]) || !\is_string($settings[$prop])) {
                throw new DatabaseException('"' . $prop . '" settings is not defined or not a string');
            }

            $setter = 'set' . \ucfirst($prop);
            $this->{$setter}($settings[$prop]);
        }
    }

    /**
     * @param array $settings
     */
    protected function setOptionalsParameters(array $settings): void
    {
        if (isset($settings['save_queries'])) {
            $this->saveQueries = (bool) $settings['save_queries'];
        }

        if (isset($settings['persistent_connection'])) {
            $this->persistentConnection = (bool) $settings['persistent_connection'];
        }

        if (\array_key_exists('charset', $settings)) {
            $this->setCharset($settings['charset']);
        }

        if (\array_key_exists('parameters', $settings)) {
            $this->setParameters($settings['parameters']);
        }
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * @param string $driver
     *
     * @throws DatabaseException
     */
    public function setDriver(string $driver): void
    {
        $availableDrivers = PDO::getAvailableDrivers();
        if (!\in_array($driver, $availableDrivers, true)) {
            throw new DatabaseException('The driver "' . $driver . '" is not available for PDO');
        }

        $this->driver = $driver;

        if ($this->getCharset() === null) {
            $this->setDriverDefaultCharset();
        }
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function setUser(string $user): void
    {
        $this->user = $user;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getDatabase(): string
    {
        return $this->database;
    }

    public function setDatabase(string $database): void
    {
        $this->database = $database;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param $key
     * @param $value
     *
     * @throws DatabaseException
     */
    public function setParameter($key, $value): void
    {
        $errorModeAttributeKeys = [PDO::ATTR_ERRMODE, (string) PDO::ATTR_ERRMODE];
        if ($value !== PDO::ERRMODE_EXCEPTION && \in_array($key, $errorModeAttributeKeys, true)) {
            $message = 'Database module only support error mode with exception. You can\'t modify this setting';
            throw new DatabaseException($message);
        }

        $this->parameters[$key] = $value;
    }

    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    public function getParametersForPDO(): array
    {
        $parameters = $this->getParameters();

        if ($this->getDriver() === 'mysql') {
            $parameters[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES ' . $this->getCharset();
        }

        $parameters[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        $parameters[PDO::ATTR_PERSISTENT] = $this->persistentConnection;

        return $parameters;
    }

    public function hasSavedQueries(): bool
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

    public function hasPersistentConnection(): bool
    {
        return $this->persistentConnection;
    }

    public function enablePersistentConnection(): void
    {
        $this->persistentConnection = true;
    }

    public function disablePersistentConnection(): void
    {
        $this->persistentConnection = false;
    }

    public function getCharset(): ?string
    {
        return $this->charset;
    }

    public function setCharset(?string $charset): void
    {
        $this->charset = $charset;
    }

    public function setDriverDefaultCharset(): ?string
    {
        $charset = static::$defaultCharsetByDriver[$this->driver] ?? null;

        $this->setCharset($charset);

        return $charset;
    }

    public function getDSN(): string
    {
        $driver = $this->getDriver();
        $dsnFormat = static::$dsnFormats[$driver] ?? static::$defaultDSN;

        return \sprintf(
            $dsnFormat,
            $driver,
            $this->getHost(),
            $this->getDatabase()
        );
    }

    /**
     * @throws DatabaseException
     */
    public function createPDOConnection(): PDO
    {
        $user = $this->getUser();
        $password = $this->getPassword();
        $parameters = $this->getParametersForPDO();
        $dsn = $this->getDSN();

        try {
            if ($this->getDriver() === 'sqlite') {
                $user = null;
                $password = null;
            }

            $pdo = new PDO($dsn, $user, $password, $parameters);

            if ($this->getDriver() === 'pgsql' && !empty($this->getCharset())) {
                $pdo->exec('SET NAMES \'' . $this->getCharset() . '\'');
            }

            return $pdo;
        } catch (PDOException $e) {
            throw new DatabaseException($e->getMessage());
        }
    }
}
