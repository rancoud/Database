<?php

namespace Rancoud\Database\Test;

use Exception;
use PDO;
use PHPUnit\Framework\TestCase;
use Rancoud\Database\Configurator;

/**
 * Class ConfiguratorTest.
 */
class ConfiguratorTest extends TestCase
{
    public function testConstructMandatory()
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => '127.0.0.1',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        static::assertSame('mysql', $conf->getEngine());
        static::assertSame('127.0.0.1', $conf->getHost());
        static::assertSame('root', $conf->getUser());
        static::assertSame('', $conf->getPassword());
        static::assertSame('test_database', $conf->getDatabase());
    }

    public function testConstructInvalidSettingsException()
    {
        static::expectException(Exception::class);
        $params = ['azerty' => true];
        new Configurator($params);
    }

    public function testConstructMandatoryException()
    {
        static::expectException(Exception::class);
        $params = [
            'database'      => 'test_database'
        ];
        new Configurator($params);
    }

    public function testConstructMandatoryEngineException()
    {
        static::expectException(Exception::class);
        $params = [
            'engine'        => 'engine',
            'host'          => '127.0.0.1',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        new Configurator($params);
    }

    public function testConstructReportErrorException()
    {
        static::expectException(Exception::class);
        $params = [
            'engine'        => 'mysql',
            'host'          => '127.0.0.1',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database',
            'report_error'  => 'report_error'
        ];
        new Configurator($params);
    }

    public function testConstructInvalidSettingsParametersException()
    {
        static::expectException(\TypeError::class);
        $params = [
            'engine'        => 'mysql',
            'host'          => '127.0.0.1',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database',
            'parameters'    => 'parameters'
        ];
        new Configurator($params);
    }

    public function testSetEngine()
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => '127.0.0.1',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        $conf->setEngine('sqlite');
        static::assertSame('sqlite', $conf->getEngine());
    }

    public function testSetEngineException()
    {
        static::expectException(Exception::class);
        $params = [
            'engine'        => 'mysql',
            'host'          => '127.0.0.1',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        $conf->setEngine('engine');
    }

    public function testSetHost()
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => '127.0.0.1',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        $conf->setHost('host');
        static::assertSame('host', $conf->getHost());
    }

    public function testSetUser()
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => '127.0.0.1',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        $conf->setUser('user');
        static::assertSame('user', $conf->getUser());
    }

    public function testSetPassword()
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => '127.0.0.1',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        $conf->setPassword('password');
        static::assertSame('password', $conf->getPassword());
    }

    public function testSetDatabase()
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => '127.0.0.1',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        $conf->setDatabase('database');
        static::assertSame('database', $conf->getDatabase());
    }

    public function testDefaultSaveQueriesFalse()
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => '127.0.0.1',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        static::assertFalse($conf->hasSaveQueries());
    }

    public function testSaveQueries()
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => '127.0.0.1',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database',
            'save_queries'  => true
        ];
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
    }

    public function testDefaultPermanentConnectionFalse()
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => '127.0.0.1',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        static::assertFalse($conf->hasPermanentConnection());
    }

    public function testPermanentConnection()
    {
        $params = [
            'engine'                => 'mysql',
            'host'                  => '127.0.0.1',
            'user'                  => 'root',
            'password'              => '',
            'database'              => 'test_database',
            'permanent_connection'  => true
        ];
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
    }

    public function testDefaultReportErrorException()
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => '127.0.0.1',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        static::assertSame('exception', $conf->getReportError());
    }

    public function testReportError()
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => '127.0.0.1',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database',
            'report_error'  => 'silent'
        ];
        $conf = new Configurator($params);

        static::assertSame('silent', $conf->getReportError());

        static::assertFalse($conf->hasThrowException());

        $conf->setReportError('exception');

        static::assertSame('exception', $conf->getReportError());

        static::assertTrue($conf->hasThrowException());
    }

    public function testSetReportErrorException()
    {
        static::expectException(Exception::class);
        $params = [
            'engine'        => 'mysql',
            'host'          => '127.0.0.1',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database',
        ];
        $conf = new Configurator($params);

        $conf->setReportError('report_error');
    }

    public function testDefaultGetCharsetUtf8()
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => '127.0.0.1',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        static::assertSame('utf8', $conf->getCharset());
    }

    public function testSetCharset()
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => '127.0.0.1',
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

    public function testDefaultGetParametersEmpty()
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => '127.0.0.1',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        static::assertSame([], $conf->getParameters());
    }

    public function testSetParameterKeyValue()
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => '127.0.0.1',
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

    public function testSetParameters()
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => '127.0.0.1',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        $conf->setParameters(['key' => 'value']);

        static::assertSame(['key' => 'value'], $conf->getParameters());
    }

    public function testGetDsnMysql()
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => '127.0.0.1',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        static::assertSame('mysql:host=127.0.0.1;dbname=test_database', $conf->getDsn());
    }

    public function testGetDsnSqlite()
    {
        $params = [
            'engine'        => 'sqlite',
            'host'          => '',
            'user'          => '',
            'password'      => '',
            'database'      => __DIR__ . '/test_database.db'
        ];
        $conf = new Configurator($params);

        static::assertSame('sqlite:' . __DIR__ . '/test_database.db', $conf->getDsn());
    }

    public function testGetDsnPgsql()
    {
        $params = [
            'engine'        => 'pgsql',
            'host'          => '127.0.0.1',
            'user'          => 'postgres',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        static::assertSame('pgsql:host=127.0.0.1;dbname=test_database', $conf->getDsn());
    }

    public function testGetParametersForPDOMysql()
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => '127.0.0.1',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        $expected = [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT         => false
        ];
        static::assertSame($expected, $conf->getParametersForPDO());

        $conf->setReportError('silent');
        $expected = [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_SILENT,
            PDO::ATTR_PERSISTENT         => false
        ];
        static::assertSame($expected, $conf->getParametersForPDO());

        $conf->setCharset('charset');
        $expected = [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES charset',
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_SILENT,
            PDO::ATTR_PERSISTENT         => false
        ];
        static::assertSame($expected, $conf->getParametersForPDO());

        $conf->enablePermanentConnection();
        $expected = [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES charset',
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_SILENT,
            PDO::ATTR_PERSISTENT         => true
        ];
        static::assertSame($expected, $conf->getParametersForPDO());
    }

    public function testGetParametersForPDOSqlite()
    {
        $params = [
            'engine'        => 'sqlite',
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

        $conf->setReportError('silent');
        $expected = [
            PDO::ATTR_ERRMODE    => PDO::ERRMODE_SILENT,
            PDO::ATTR_PERSISTENT => false
        ];
        static::assertSame($expected, $conf->getParametersForPDO());

        $conf->setCharset('charset');
        $expected = [
            PDO::ATTR_ERRMODE    => PDO::ERRMODE_SILENT,
            PDO::ATTR_PERSISTENT => false
        ];
        static::assertSame($expected, $conf->getParametersForPDO());

        $conf->enablePermanentConnection();
        $expected = [
            PDO::ATTR_ERRMODE    => PDO::ERRMODE_SILENT,
            PDO::ATTR_PERSISTENT => true
        ];
        static::assertSame($expected, $conf->getParametersForPDO());
    }

    public function testCreatePDOConnectionMysqlInReportErrorException()
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => '127.0.0.1',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        static::assertNotNull($conf->createPDOConnection());
    }

    public function testCreatePDOConnectionMysqlInReportErrorExceptionThrowException()
    {
        static::expectException(Exception::class);

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

    public function testCreatePDOConnectionMysqlInReportErrorSilent()
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => '127.0.0.1',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database',
            'report_error'  => 'silent'
        ];
        $conf = new Configurator($params);

        static::assertNotNull($conf->createPDOConnection());
    }

    public function testCreatePDOConnectionMysqlInReportErrorSilentErrorThrowException()
    {
        static::expectException(Exception::class);

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

    public function testCreatePDOConnectionSqlite()
    {
        $params = [
            'engine'        => 'sqlite',
            'host'          => '',
            'user'          => '',
            'password'      => '',
            'database'      => __DIR__ . '/test_database.db'
        ];
        $conf = new Configurator($params);

        static::assertNotNull($conf->createPDOConnection());
    }

    public function testCreatePDOConnectionPgsql()
    {
        $params = [
            'engine'        => 'pgsql',
            'host'          => '127.0.0.1',
            'user'          => 'postgres',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        static::assertNotNull($conf->createPDOConnection());
    }
}
