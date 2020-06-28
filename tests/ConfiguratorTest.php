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
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        try {
            $conf = new Configurator($params);

            static::assertSame('mysql', $conf->getEngine());
            static::assertSame('localhost', $conf->getHost());
            static::assertSame('root', $conf->getUser());
            static::assertSame('', $conf->getPassword());
            static::assertSame('test_database', $conf->getDatabase());
        } catch (DatabaseException $e) {
            throw $e;
        }
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
        $this->expectExceptionMessage('"engine" settings is not defined or not a string');
        
        $params = [
            'database'      => 'test_database'
        ];

        new Configurator($params);
    }

    public function testConstructMandatoryEngineException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('The engine "engine" is not available for PDO');
        
        $params = [
            'engine'        => 'engine',
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
            'engine'        => 'mysql',
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
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        try {
            $conf = new Configurator($params);

            $conf->setEngine('sqlite');
            static::assertSame('sqlite', $conf->getEngine());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    public function testSetEngineException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('The engine "engine" is not available for PDO');

        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        $conf = new Configurator($params);

        $conf->setEngine('engine');
    }

    /**
     * @throws DatabaseException
     */
    public function testSetHost(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        try {
            $conf = new Configurator($params);

            $conf->setHost('host');
            static::assertSame('host', $conf->getHost());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testSetUser(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        try {
            $conf = new Configurator($params);

            $conf->setUser('user');
            static::assertSame('user', $conf->getUser());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testSetPassword(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        try {
            $conf = new Configurator($params);

            $conf->setPassword('password');
            static::assertSame('password', $conf->getPassword());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testSetDatabase(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        try {
            $conf = new Configurator($params);

            $conf->setDatabase('database');
            static::assertSame('database', $conf->getDatabase());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testDefaultSaveQueriesFalse(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        try {
            $conf = new Configurator($params);

            static::assertFalse($conf->hasSaveQueries());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testSaveQueries(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database',
            'save_queries'  => true
        ];

        try {
            $conf = new Configurator($params);

            static::assertTrue($conf->hasSaveQueries());

            $conf->disableSaveQueries();

            static::assertFalse($conf->hasSaveQueries());

            $conf->disableSaveQueries();

            static::assertFalse($conf->hasSaveQueries());

            $conf->enableSaveQueries();

            static::assertTrue($conf->hasSaveQueries());

            $conf->enableSaveQueries();

            static::assertTrue($conf->hasSaveQueries());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testDefaultPermanentConnectionFalse(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        try {
            $conf = new Configurator($params);

            static::assertFalse($conf->hasPermanentConnection());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testPermanentConnection(): void
    {
        $params = [
            'engine'                => 'mysql',
            'host'                  => 'localhost',
            'user'                  => 'root',
            'password'              => '',
            'database'              => 'test_database',
            'permanent_connection'  => true
        ];

        try {
            $conf = new Configurator($params);

            static::assertTrue($conf->hasPermanentConnection());

            $conf->disablePermanentConnection();

            static::assertFalse($conf->hasPermanentConnection());

            $conf->disablePermanentConnection();

            static::assertFalse($conf->hasPermanentConnection());

            $conf->enablePermanentConnection();

            static::assertTrue($conf->hasPermanentConnection());

            $conf->enablePermanentConnection();

            static::assertTrue($conf->hasPermanentConnection());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testDefaultGetCharsetUtf8mb4(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        try {
            $conf = new Configurator($params);

            static::assertSame('utf8mb4', $conf->getCharset());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testSetCharset(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database',
            'charset'       => 'charset'
        ];

        try {
            $conf = new Configurator($params);

            static::assertSame('charset', $conf->getCharset());

            $conf->setCharset('new_charset');

            static::assertSame('new_charset', $conf->getCharset());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testDefaultGetParametersEmpty(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        try {
            $conf = new Configurator($params);

            static::assertSame([], $conf->getParameters());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testSetParameterKeyValue(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database',
            'parameters'    => ['key' => 'value']
        ];

        try {
            $conf = new Configurator($params);

            static::assertSame(['key' => 'value'], $conf->getParameters());

            $conf->setParameter('new_key', 'new_value');

            static::assertSame(['key' => 'value', 'new_key' => 'new_value'], $conf->getParameters());

            $conf->setParameter('key', 'another_value');

            static::assertSame(['key' => 'another_value', 'new_key' => 'new_value'], $conf->getParameters());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testSetParameters(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        try {
            $conf = new Configurator($params);

            $conf->setParameters(['key' => 'value']);

            static::assertSame(['key' => 'value'], $conf->getParameters());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testGetDsnMysql(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        try {
            $conf = new Configurator($params);

            static::assertSame('mysql:host=localhost;dbname=test_database', $conf->getDsn());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testGetDsnSqlite(): void
    {
        $params = [
            'engine'        => 'sqlite',
            'host'          => '',
            'user'          => '',
            'password'      => '',
            'database'      => __DIR__ . '/test_database.db'
        ];

        try {
            $conf = new Configurator($params);

            static::assertSame('sqlite:' . __DIR__ . '/test_database.db', $conf->getDsn());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testGetDsnPgsql(): void
    {
        $params = [
            'engine'        => 'pgsql',
            'host'          => '127.0.0.1',
            'user'          => 'postgres',
            'password'      => '',
            'database'      => 'test_database'
        ];

        try {
            $conf = new Configurator($params);

            static::assertSame('pgsql:host=127.0.0.1;dbname=test_database', $conf->getDsn());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testGetParametersForPDOMysql(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => '127.0.0.1',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        try {
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

            $conf->enablePermanentConnection();
            $expected = [
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES charset',
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_PERSISTENT         => true
            ];
            static::assertSame($expected, $conf->getParametersForPDO());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testGetParametersForPDOSqlite(): void
    {
        $params = [
            'engine'        => 'sqlite',
            'host'          => '',
            'user'          => '',
            'password'      => '',
            'database'      => __DIR__ . '/test_database.db'
        ];

        try {
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

            $conf->enablePermanentConnection();
            $expected = [
                PDO::ATTR_ERRMODE    => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_PERSISTENT => true
            ];
            static::assertSame($expected, $conf->getParametersForPDO());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testGetParametersForPDOPgsql(): void
    {
        $params = [
            'engine'        => 'pgsql',
            'host'          => '127.0.0.1',
            'user'          => 'postgres',
            'password'      => '',
            'database'      => 'test_database'
        ];

        try {
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

            $conf->enablePermanentConnection();
            $expected = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_PERSISTENT => true
            ];
            static::assertSame($expected, $conf->getParametersForPDO());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testCreatePDOConnectionMysqlInReportErrorException(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => '127.0.0.1',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        try {
            $conf = new Configurator($params);

            static::assertNotNull($conf->createPDOConnection());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    public function testCreatePDOConnectionMysqlInReportErrorExceptionThrowException(): void
    {
        $this->expectException(DatabaseException::class);

        $params = [
            'engine'        => 'mysql',
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
    public function testCreatePDOConnectionMysqlInReportErrorSilent(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => '127.0.0.1',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];

        try {
            $conf = new Configurator($params);

            static::assertNotNull($conf->createPDOConnection());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    public function testCreatePDOConnectionMysqlInReportErrorSilentErrorThrowException(): void
    {
        $this->expectException(DatabaseException::class);

        $params = [
            'engine'        => 'mysql',
            'host'          => '127.0.0.1',
            'user'          => 'root',
            'password'      => 'root',
            'database'      => 'test_database',
            'report_error'  => 'silent'
        ];

        $conf = new Configurator($params);

        $conf->createPDOConnection();
    }

    /**
     * @throws DatabaseException
     */
    public function testCreatePDOConnectionPgsqlInReportErrorException(): void
    {
        $params = [
            'engine'        => 'pgsql',
            'host'          => '127.0.0.1',
            'user'          => 'postgres',
            'password'      => '',
            'database'      => 'test_database'
        ];

        try {
            $conf = new Configurator($params);

            static::assertNotNull($conf->createPDOConnection());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testCreatePDOConnectionPgsqlInReportErrorSilent(): void
    {
        $params = [
            'engine'        => 'pgsql',
            'host'          => '127.0.0.1',
            'user'          => 'postgres',
            'password'      => '',
            'database'      => 'test_database'
        ];

        try {
            $conf = new Configurator($params);

            static::assertNotNull($conf->createPDOConnection());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testCreatePDOConnectionSqlite(): void
    {
        $params = [
            'engine'        => 'sqlite',
            'host'          => '',
            'user'          => '',
            'password'      => '',
            'database'      => __DIR__ . '/test_database.db'
        ];

        try {
            $conf = new Configurator($params);

            static::assertNotNull($conf->createPDOConnection());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }
}
