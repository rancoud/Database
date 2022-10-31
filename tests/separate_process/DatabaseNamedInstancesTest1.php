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
class DatabaseNamedInstancesTest1 extends TestCase
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
}
