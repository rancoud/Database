<?php
/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection SqlDialectInspection */

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
    /** @var Database|null */
    protected ?Database $db;

    protected array $params = [
        'driver'       => 'mysql',
        'host'         => 'localhost',
        'user'         => 'root',
        'password'     => '',
        'database'     => 'test_database'
    ];

    /**
     * @throws DatabaseException
     */
    public function setUp(): void
    {
        $databaseConf = new Configurator($this->params);
        $this->db = new Database($databaseConf);
    }

    /** @runInSeparateProcess  */
    public function testSingletonEmptyConfiguratorException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Configurator Missing');

        Database::getInstance();
    }

    /**
     * @runInSeparateProcess
     * @throws DatabaseException
     */
    public function testSingleton(): void
    {
		Database::getInstance(new Configurator($this->params));
		$this->expectNotToPerformAssertions();
    }

    /** @runInSeparateProcess  */
    public function testSingletonCallTwiceWithConfiguratorException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Configurator Already Setup');

        Database::getInstance(new Configurator($this->params));
        Database::getInstance(new Configurator($this->params));
    }
}
