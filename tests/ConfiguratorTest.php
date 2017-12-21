<?php

namespace Rancoud\Database\Test;

use Exception;
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
/*
        $conf->getEngine();
        $conf->setEngine('');
        $conf->getHost();
        $conf->setHost('');
        $conf->getUser();
        $conf->setUser('');
        $conf->getPassword();
        $conf->setPassword('');
        $conf->getDatabase();
        $conf->setDatabase('');
        $conf->getParameters();
        $conf->setParameter('', '');
        $conf->getParametersForPDO();
        $conf->hasSaveQueries();
        $conf->enableSaveQueries();
        $conf->disableSaveQueries();
        $conf->getReportError();
        $conf->setReportError('');
        $conf->hasThrowException();
        $conf->getCharset();
        $conf->setCharset('');
        $conf->getDsn();
        $conf->createPDOConnection();
*/
    }

    public function testConstructMandatoryException()
    {
        static::expectException(Exception::class);
        $params = [];
        new Configurator($params);
    }

    public function testConstructMandatoryEngineException()
    {
        static::expectException(Exception::class);
        $params = [
            'engine'        => 'engine',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        new Configurator($params);
    }

    public function testGetDsnMysql()
    {
        $params = [
            'engine'        => 'mysql',
            'host'          => 'localhost',
            'user'          => 'root',
            'password'      => '',
            'database'      => 'test_database'
        ];
        $conf = new Configurator($params);

        static::assertSame('mysql:host=localhost;dbname=test_database;charset=utf8', $conf->getDsn());
    }

    public function testGetDsnSqlite()
    {
        $params = [
            'engine'        => 'sqlite',
            'host'          => '',
            'user'          => '',
            'password'      => '',
            'database'      => 'test_database.db'
        ];
        $conf = new Configurator($params);

        static::assertSame('sqlite:test_database.db;charset=utf8', $conf->getDsn());
    }
}
