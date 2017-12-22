<?php

namespace Rancoud\Database\Test;

use Exception;
use PHPUnit\Framework\TestCase;
use Rancoud\Database\Configurator;
use Rancoud\Database\Database;

/**
 * Class DatabaseSingletonTest.
 */
class DatabaseSingletonTest extends TestCase
{
    /** @var Database */
    protected $db;

    protected $params = ['engine' => 'mysql',
        'host'          => 'localhost',
        'user'          => 'root',
        'password'      => '',
        'database'      => 'test_database',
        'report_error'  => 'exception'];

    public function setUp()
    {
        $databaseConf = new Configurator($this->params);
        $this->db = new Database($databaseConf);
    }

    public function testSingletonEmptyConfiguratorException()
    {
        static::expectException(Exception::class);

        Database::getInstance();
    }

    public function testSingleton()
    {
        $db = Database::getInstance(new Configurator($this->params));

        static::assertSame(get_class($db), 'Rancoud\Database\Database');
    }

    public function testSingletonCallTwiceWithConfiguratorException()
    {
        static::expectException(Exception::class);

        Database::getInstance(new Configurator($this->params));
    }
}
