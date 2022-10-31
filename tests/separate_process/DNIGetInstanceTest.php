<?php

/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection SqlDialectInspection
 */

declare(strict_types=1);

namespace tests\separate_process;

use PHPUnit\Framework\TestCase;
use Rancoud\Database\Configurator;
use Rancoud\Database\Database;
use Rancoud\Database\DatabaseException;

/**
 * Class DNIGetInstanceTest.
 */
class DNIGetInstanceTest extends TestCase
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
    public function testGetInstance(): void
    {
        static::assertNull(Database::getInstance());
        static::assertNull(Database::getInstance('secondary'));

        $db1 = Database::setInstance(new Configurator($this->params));
        $db2 = Database::setInstance(new Configurator($this->params), 'secondary');

        static::assertInstanceOf(Database::class, Database::getInstance());
        static::assertInstanceOf(Database::class, Database::getInstance('secondary'));

        static::assertSame($db1, Database::getInstance());
        static::assertSame($db2, Database::getInstance('secondary'));
    }
}
