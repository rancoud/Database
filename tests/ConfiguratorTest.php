<?php

declare(strict_types=1);

namespace Rancoud\Database\Test;

use PDO;
use PHPUnit\Framework\TestCase;
use Rancoud\Database\Configurator;
use Rancoud\Database\DatabaseException;
use TypeError;

/**
 * Class ConfiguratorTest.
 */
class ConfiguratorTest extends TestCase
{
    public function testConstructMandatory(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        static::assertSame('mysql', $conf->getEngine());
        static::assertSame('localhost', $conf->getHost());
        static::assertSame('root', $conf->getUser());
        static::assertSame('', $conf->getPassword());
        static::assertSame('test_database', $conf->getDatabase());
    }

    public function testConstructInvalidSettingsException(): void
    {
        static::expectException(DatabaseException::class);
        static::expectExceptionMessage('"azerty" settings is not recognized');

        $params = ['azerty' => true];
        new Configurator($params);
    }

    public function testConstructMandatoryException(): void
    {
        static::expectException(DatabaseException::class);
        static::expectExceptionMessage('"engine" settings is not defined or not a string');
        
        $params = [
            'database'      => 'test_database'
        ];
        new Configurator($params);
    }

    public function testConstructMandatoryEngineException(): void
    {
        static::expectException(DatabaseException::class);
        static::expectExceptionMessage('The engine "engine" is not available for PDO');
        
        $params = [
            'engine'        => 'engine',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        new Configurator($params);
    }

    public function testConstructReportErrorException(): void
    {
        static::expectException(DatabaseException::class);
        static::expectExceptionMessage('The report error "report_error" is incorrect. (silent , exception)');
        
        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database',
            'report_error'  => 'report_error'
        ];
        new Configurator($params);
    }

    public function testConstructInvalidSettingsParametersException(): void
    {
        static::expectException(TypeError::class);
        
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

    public function testSetEngine(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        $conf->setEngine('sqlite');
        static::assertSame('sqlite', $conf->getEngine());
    }

    public function testSetEngineException(): void
    {
        static::expectException(DatabaseException::class);
        static::expectExceptionMessage('The engine "engine" is not available for PDO');

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

    public function testSetHost(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        $conf->setHost('host');
        static::assertSame('host', $conf->getHost());
    }

    public function testSetUser(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        $conf->setUser('user');
        static::assertSame('user', $conf->getUser());
    }

    public function testSetPassword(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        $conf->setPassword('password');
        static::assertSame('password', $conf->getPassword());
    }

    public function testSetDatabase(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        $conf->setDatabase('database');
        static::assertSame('database', $conf->getDatabase());
    }

    public function testDefaultSaveQueriesFalse(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        static::assertFalse($conf->hasSaveQueries());
    }

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

    public function testDefaultPermanentConnectionFalse(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        static::assertFalse($conf->hasPermanentConnection());
    }

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

    public function testDefaultReportErrorException(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        static::assertSame('exception', $conf->getReportError());
    }

    public function testReportError(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
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

    public function testSetReportErrorException(): void
    {
        static::expectException(DatabaseException::class);
        static::expectExceptionMessage('The report error "report_error" is incorrect. (silent , exception)');

        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database',
        ];
        $conf = new Configurator($params);

        $conf->setReportError('report_error');
    }

    public function testDefaultGetCharsetUtf8(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        static::assertSame('utf8', $conf->getCharset());
    }

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
        $conf = new Configurator($params);

        static::assertSame('charset', $conf->getCharset());

        $conf->setCharset('new_charset');

        static::assertSame('new_charset', $conf->getCharset());
    }

    public function testDefaultGetParametersEmpty(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        static::assertSame([], $conf->getParameters());
    }

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
        $conf = new Configurator($params);

        static::assertSame(['key' => 'value'], $conf->getParameters());

        $conf->setParameter('new_key', 'new_value');

        static::assertSame(['key' => 'value', 'new_key' => 'new_value'], $conf->getParameters());

        $conf->setParameter('key', 'another_value');

        static::assertSame(['key' => 'another_value', 'new_key' => 'new_value'], $conf->getParameters());
    }

    public function testSetParameters(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        $conf->setParameters(['key' => 'value']);

        static::assertSame(['key' => 'value'], $conf->getParameters());
    }

    public function testGetDsnMysql(): void
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        static::assertSame('mysql:host=localhost;dbname=test_database', $conf->getDsn());
    }

    public function testGetDsnSqlite(): void
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

    public function testGetDsnPgsql(): void
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

    public function testGetParametersForPDOMysql(): void
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

    public function testGetParametersForPDOSqlite(): void
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

    public function testGetParametersForPDOPgsql(): void
    {
        $params = [
            'engine'        => 'pgsql',
            'host'          => '127.0.0.1',
            'user'          => 'postgres',
            'password'      => '',
            'database'      => 'test_database'
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

    public function testCreatePDOConnectionMysqlInReportErrorException(): void
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

    public function testCreatePDOConnectionMysqlInReportErrorExceptionThrowException(): void
    {
        static::expectException(DatabaseException::class);
        //static::expectExceptionMessage("SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost' (using password: YES)");

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

    public function testCreatePDOConnectionMysqlInReportErrorSilent(): void
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

    public function testCreatePDOConnectionMysqlInReportErrorSilentErrorThrowException(): void
    {
        static::expectException(DatabaseException::class);
        //static::expectExceptionMessage("SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost' (using password: YES)");

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

    public function testCreatePDOConnectionPgsqlInReportErrorException(): void
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

    public function testCreatePDOConnectionPgsqlInReportErrorSilent(): void
    {
        $params = [
            'engine'        => 'pgsql',
            'host'          => '127.0.0.1',
            'user'          => 'postgres',
            'password'      => '',
            'database'      => 'test_database',
            'report_error'  => 'silent'
        ];
        $conf = new Configurator($params);

        static::assertNotNull($conf->createPDOConnection());
    }

    public function testCreatePDOConnectionSqlite(): void
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
}
