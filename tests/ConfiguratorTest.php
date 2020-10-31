<?php
/** @noinspection PhpIllegalPsrClassPathInspection */

declare(strict_types=1);

namespace Rancoud\Database\Test;

use PDO;
use PHPUnit\Framework\TestCase;
use Rancoud\Database\Configurator;
use Rancoud\Database\DatabaseException;

/**
 * Class ConfiguratorTest.
 */
class ConfiguratorTest extends TestCase
{
    /**
     * @throws DatabaseException
     */
    public function testConstructMandatory(): void
    {
        $params = [
            'driver'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        $conf = new Configurator($params);

        static::assertSame('mysql', $conf->getDriver());
        static::assertSame('localhost', $conf->getHost());
        static::assertSame('root', $conf->getUser());
        static::assertSame('', $conf->getPassword());
        static::assertSame('test_database', $conf->getDatabase());
    }

    public function testConstructInvalidSettingsException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('"azerty" settings is not recognized');

        $params = ['azerty' => true];

        new Configurator($params);
    }

    public function testConstructMandatoryException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('"driver" settings is not defined or not a string');

        $params = [
            'database'      => 'test_database'
        ];

        new Configurator($params);
    }

    public function testConstructMandatoryEngineException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('The driver "driver" is not available for PDO');

        $params = [
            'driver'        => 'driver',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        new Configurator($params);
    }

    /**
     * @throws DatabaseException
     * @noinspection PhpUndefinedClassInspection
     */
    public function testConstructInvalidSettingsParametersException(): void
    {
        $this->expectException(\TypeError::class);

        $params = [
            'driver'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database',
            'parameters'    => 'parameters'
        ];

        new Configurator($params);
    }

    /**
     * @throws DatabaseException
     */
    public function testSetEngine(): void
    {
        $params = [
            'driver'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        $conf = new Configurator($params);

        $conf->setDriver('sqlite');
        static::assertSame('sqlite', $conf->getDriver());
    }

    public function testSetEngineException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('The driver "driver" is not available for PDO');

        $params = [
            'driver'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        $conf = new Configurator($params);

        $conf->setDriver('driver');
    }

    /**
     * @throws DatabaseException
     */
    public function testSetHost(): void
    {
        $params = [
            'driver'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        $conf = new Configurator($params);

        $conf->setHost('host');
        static::assertSame('host', $conf->getHost());
    }

    /**
     * @throws DatabaseException
     */
    public function testSetUser(): void
    {
        $params = [
            'driver'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        $conf = new Configurator($params);
        $conf->setUser('user');

        static::assertSame('user', $conf->getUser());
    }

    /**
     * @throws DatabaseException
     */
    public function testSetPassword(): void
    {
        $params = [
            'driver'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        $conf = new Configurator($params);
        $conf->setPassword('password');

        static::assertSame('password', $conf->getPassword());
    }

    /**
     * @throws DatabaseException
     */
    public function testSetDatabase(): void
    {
        $params = [
            'driver'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        $conf = new Configurator($params);
        $conf->setDatabase('database');

        static::assertSame('database', $conf->getDatabase());
    }

    /**
     * @throws DatabaseException
     */
    public function testDefaultSaveQueriesFalse(): void
    {
        $params = [
            'driver'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        $conf = new Configurator($params);

        static::assertFalse($conf->hasSavedQueries());
    }

    /**
     * @throws DatabaseException
     */
    public function testSaveQueries(): void
    {
        $params = [
            'driver'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database',
            'save_queries'  => true
        ];

        $conf = new Configurator($params);

        static::assertTrue($conf->hasSavedQueries());

        $conf->disableSaveQueries();

        static::assertFalse($conf->hasSavedQueries());

        $conf->disableSaveQueries();

        static::assertFalse($conf->hasSavedQueries());

        $conf->enableSaveQueries();

        static::assertTrue($conf->hasSavedQueries());

        $conf->enableSaveQueries();

        static::assertTrue($conf->hasSavedQueries());
    }

    /**
     * @throws DatabaseException
     */
    public function testDefaultPersistentConnectionFalse(): void
    {
        $params = [
            'driver'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        $conf = new Configurator($params);

        static::assertFalse($conf->hasPersistentConnection());
    }

    /**
     * @throws DatabaseException
     */
    public function testPersistentConnection(): void
    {
        $params = [
            'driver'                => 'mysql',
            'host'                  => 'localhost',
            'user'                  => 'root',
            'password'              => '',
            'database'              => 'test_database',
            'persistent_connection' => true
        ];

        $conf = new Configurator($params);

        static::assertTrue($conf->hasPersistentConnection());

        $conf->disablePersistentConnection();

        static::assertFalse($conf->hasPersistentConnection());

        $conf->disablePersistentConnection();

        static::assertFalse($conf->hasPersistentConnection());

        $conf->enablePersistentConnection();

        static::assertTrue($conf->hasPersistentConnection());

        $conf->enablePersistentConnection();

        static::assertTrue($conf->hasPersistentConnection());
    }

    /**
     * @throws DatabaseException
     */
    public function testDefaultGetCharsetUtf8mb4(): void
    {
        $params = [
            'driver'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        $conf = new Configurator($params);

        static::assertSame('utf8mb4', $conf->getCharset());
    }

    /**
     * @throws DatabaseException
     */
    public function testSetCharset(): void
    {
        $params = [
            'driver'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database',
            'charset'       => 'charset'
        ];

        $conf = new Configurator($params);

        static::assertSame('charset', $conf->getCharset());

        $conf->setCharset('new_charset');

        static::assertSame('new_charset', $conf->getCharset());
    }

    /**
     * @throws DatabaseException
     */
    public function testDefaultGetParametersEmpty(): void
    {
        $params = [
            'driver'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        $conf = new Configurator($params);

        static::assertSame([], $conf->getParameters());
    }

    /**
     * @throws DatabaseException
     */
    public function testSetParameterKeyValue(): void
    {
        $params = [
            'driver'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database',
            'parameters'    => ['key' => 'value']
        ];

        $conf = new Configurator($params);

        static::assertSame(['key' => 'value'], $conf->getParameters());

        $conf->setParameter('new_key', 'new_value');

        static::assertSame(['key' => 'value', 'new_key' => 'new_value'], $conf->getParameters());

        $conf->setParameter('key', 'another_value');

        static::assertSame(['key' => 'another_value', 'new_key' => 'new_value'], $conf->getParameters());
    }

    /**
     * @throws DatabaseException
     */
    public function testSetParameterThrowException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Database module only support error mode with exception. You can\'t modify this setting');

        $params = [
            'driver'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        $conf = new Configurator($params);
        $conf->setParameter(PDO::ATTR_ERRMODE, 1);
    }

    /**
     * @throws DatabaseException
     */
    public function testSetParameters(): void
    {
        $params = [
            'driver'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        $conf = new Configurator($params);

        $conf->setParameters(['key' => 'value']);

        static::assertSame(['key' => 'value'], $conf->getParameters());
    }

    /**
     * @throws DatabaseException
     */
    public function testGetDSNMysql(): void
    {
        $params = [
            'driver'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        $conf = new Configurator($params);

        static::assertSame('mysql:host=localhost;dbname=test_database', $conf->getDSN());
    }

    /**
     * @throws DatabaseException
     */
    public function testGetDSNSqlite(): void
    {
        $params = [
            'driver'        => 'sqlite',
            'host'          => '',
            'user'          => '',
            'password'      => '',
            'database'      => __DIR__ . '/test_database.db'
        ];

        $conf = new Configurator($params);

        static::assertSame('sqlite:' . __DIR__ . '/test_database.db', $conf->getDSN());
    }

    /**
     * @throws DatabaseException
     */
    public function testGetDSNPgsql(): void
    {
        $params = [
            'driver'        => 'pgsql',
            'host'          => '127.0.0.1',
            'user'          => 'postgres',
            'password'      => '',
            'database'      => 'test_database'
        ];

        $conf = new Configurator($params);

        static::assertSame('pgsql:host=127.0.0.1;dbname=test_database', $conf->getDSN());
    }

    /**
     * @throws DatabaseException
     */
    public function testGetParametersForPDOMysql(): void
    {
        $params = [
            'driver'        => 'mysql',
            'host'          => '127.0.0.1',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        $conf = new Configurator($params);

        $expected = [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT         => false
        ];
        static::assertSame($expected, $conf->getParametersForPDO());

        $conf->setCharset('charset');
        $expected = [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES charset',
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT         => false
        ];
        static::assertSame($expected, $conf->getParametersForPDO());

        $conf->enablePersistentConnection();
        $expected = [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES charset',
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT         => true
        ];
        static::assertSame($expected, $conf->getParametersForPDO());
    }

    /**
     * @throws DatabaseException
     */
    public function testGetParametersForPDOSqlite(): void
    {
        $params = [
            'driver'        => 'sqlite',
            'host'          => '',
            'user'          => '',
            'password'      => '',
            'database'      => __DIR__ . '/test_database.db'
        ];

        $conf = new Configurator($params);

        $expected = [
            PDO::ATTR_ERRMODE    => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => false
        ];
        static::assertSame($expected, $conf->getParametersForPDO());

        $conf->setCharset('charset');
        $expected = [
            PDO::ATTR_ERRMODE    => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => false
        ];
        static::assertSame($expected, $conf->getParametersForPDO());

        $conf->enablePersistentConnection();
        $expected = [
            PDO::ATTR_ERRMODE    => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => true
        ];
        static::assertSame($expected, $conf->getParametersForPDO());
    }

    /**
     * @throws DatabaseException
     */
    public function testGetParametersForPDOPgsql(): void
    {
        $params = [
            'driver'        => 'pgsql',
            'host'          => '127.0.0.1',
            'user'          => 'postgres',
            'password'      => '',
            'database'      => 'test_database'
        ];

        $conf = new Configurator($params);

        $expected = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => false
        ];
        static::assertSame($expected, $conf->getParametersForPDO());

        $conf->setCharset('charset');
        $expected = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => false
        ];
        static::assertSame($expected, $conf->getParametersForPDO());

        $conf->enablePersistentConnection();
        $expected = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => true
        ];
        static::assertSame($expected, $conf->getParametersForPDO());
    }

    /**
     * @throws DatabaseException
     */
    public function testCreatePDOConnectionMysql(): void
    {
        $mysqlHost = getenv('MYSQL_HOST', true);

        $params = [
            'driver'        => 'mysql',
            'host'          => ($mysqlHost !== false) ?  $mysqlHost : '127.0.0.1',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        $conf = new Configurator($params);

        static::assertNotNull($conf->createPDOConnection());
    }

    public function testCreatePDOConnectionMysqlThrowException(): void
    {
        $this->expectException(DatabaseException::class);

        $params = [
            'driver'        => 'mysql',
            'host'          => '127.0.0.1',
            'user'          => 'root',
            'password'      => 'root',
            'database'      => 'test_database'
        ];

        $conf = new Configurator($params);

        $conf->createPDOConnection();
    }

    /**
     * @throws DatabaseException
     */
    public function testCreatePDOConnectionPgsql(): void
    {
        $postgresHost = getenv('POSTGRES_HOST', true);

        $params = [
            'driver'        => 'pgsql',
            'host'          => ($postgresHost !== false) ?  $postgresHost : '127.0.0.1',
            'user'          => 'postgres',
            'password'      => 'postgres',
            'database'      => 'test_database'
        ];

        $conf = new Configurator($params);

        static::assertNotNull($conf->createPDOConnection());
    }

    /**
     * @throws DatabaseException
     */
    public function testCreatePDOConnectionPgsqlThrowException(): void
    {
        $this->expectException(DatabaseException::class);

        $params = [
            'driver'        => 'pgsql',
            'host'          => '127.0.0.1',
            'user'          => 'postgres',
            'password'      => 'postgres',
            'database'      => ''
        ];

        $conf = new Configurator($params);

        $conf->createPDOConnection();
    }

    /**
     * @throws DatabaseException
     */
    public function testCreatePDOConnectionSqlite(): void
    {
        $params = [
            'driver'        => 'sqlite',
            'host'          => '',
            'user'          => '',
            'password'      => '',
            'database'      => __DIR__ . '/test_database.db'
        ];

        $conf = new Configurator($params);

        static::assertNotNull($conf->createPDOConnection());
    }
}
