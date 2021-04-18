<?php

/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection SqlDialectInspection
 */

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\TestCase;
use Rancoud\Database\Configurator;
use Rancoud\Database\Database;
use Rancoud\Database\DatabaseException;
use ReflectionClass;

/**
 * Class DatabaseNamedInstancesTest.
 */
class DatabaseNamedInstancesTest extends TestCase
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
     * @runInSeparateProcess
     *
     * @throws DatabaseException
     */
    public function testSetInstance(): void
    {
        $class = new ReflectionClass(Database::class);

        $db1 = Database::setInstance(new Configurator($this->params));

        $properties = $class->getStaticProperties();
        static::assertNotEmpty($properties['instances']);
        static::assertArrayHasKey('primary', $properties['instances']);
        static::assertInstanceOf(Database::class, $properties['instances']['primary']);
        static::assertSame($db1, $properties['instances']['primary']);

        $db2 = Database::setInstance(new Configurator($this->params), 'secondary');
        $properties = $class->getStaticProperties();
        static::assertNotEmpty($properties['instances']);
        static::assertArrayHasKey('secondary', $properties['instances']);
        static::assertInstanceOf(Database::class, $properties['instances']['secondary']);
        static::assertSame($db2, $properties['instances']['secondary']);
    }

    /**
     * @runInSeparateProcess
     *
     * @throws DatabaseException
     */
    public function testSetInstanceThrowException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Cannot overwrite instance "primary"');

        Database::setInstance(new Configurator($this->params));
        Database::setInstance(new Configurator($this->params));
    }

    /**
     * @runInSeparateProcess
     *
     * @throws DatabaseException
     */
    public function testHasInstance(): void
    {
        static::assertFalse(Database::hasInstance());

        Database::setInstance(new Configurator($this->params));

        static::assertTrue(Database::hasInstance());

        static::assertFalse(Database::hasInstance('secondary'));

        Database::setInstance(new Configurator($this->params), 'secondary');

        static::assertTrue(Database::hasInstance('secondary'));
    }

    /**
     * @runInSeparateProcess
     *
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
