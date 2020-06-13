<?php

declare(strict_types=1);

namespace Rancoud\Database\Test;

use PHPUnit\Framework\TestCase;
use Rancoud\Database\Configurator;
use Rancoud\Database\Database;
use Rancoud\Database\DatabaseException;

/**
 * Class DatabaseSingletonTest.
 */
class DatabaseSingletonTest extends TestCase
{
    /** @var Database */
    protected $db;

    protected $params = [
        'engine'       => 'mysql',
        'host'         => 'localhost',
        'user'         => 'root',
        'password'     => '',
        'database'     => 'test_database',
        'report_error' => 'exception'
    ];

    public function setUp(): void
    {
        $databaseConf = new Configurator($this->params);
        $this->db = new Database($databaseConf);
    }
    
    /** @runInSeparateProcess  */
    public function testSingletonEmptyConfiguratorException(): void
    {
        static::expectException(DatabaseException::class);
        static::expectExceptionMessage('Configurator Missing');

        Database::getInstance();
    }
    
    /** @runInSeparateProcess  */
    public function testSingleton(): void
    {
        $db = Database::getInstance(new Configurator($this->params));

        static::assertSame(get_class($db), 'Rancoud\Database\Database');
    }

    /** @runInSeparateProcess  */
    public function testSingletonCallTwiceWithConfiguratorException(): void
    {
        static::expectException(DatabaseException::class);
        static::expectExceptionMessage('Configurator Already Setup');

        Database::getInstance(new Configurator($this->params));
        Database::getInstance(new Configurator($this->params));
    }
}
