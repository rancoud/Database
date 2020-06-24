<?php
/** @noinspection ForgottenDebugOutputInspection */
/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection SqlDialectInspection */

declare(strict_types=1);

namespace Rancoud\Database\Test;

use PDOStatement;
use PHPUnit\Framework\TestCase;
use Rancoud\Database\Configurator;
use Rancoud\Database\Database;
use Rancoud\Database\DatabaseException;

/**
 * Class DatabaseTest.
 */
class DatabaseTest extends TestCase
{
    protected array $sgbds = [
        'mysql_exception' => [
            /** @var ?Database $db; */
            'db' => null,
            'parameters' => [
                'engine'       => 'mysql',
                'host'         => '127.0.0.1',
                'user'         => 'root',
                'password'     => '',
                'database'     => 'test_database',
                'report_error' => 'exception'
            ],
        ],
        'mysql_silent' => [
            /** @var ?Database $db; */
            'db' => null,
            'parameters' => [
                'engine'       => 'mysql',
                'host'         => '127.0.0.1',
                'user'         => 'root',
                'password'     => '',
                'database'     => 'test_database',
                'report_error' => 'silent'
            ],
        ],
        'postgresql_exception' => [
            /** @var ?Database $db; */
            'db' => null,
            'parameters' => [
                'engine'        => 'pgsql',
                'host'          => '127.0.0.1',
                'user'          => 'postgres',
                'password'      => '',
                'database'      => 'test_database',
                'report_error'  => 'exception'
            ],
        ],
        'sqlite_exception' => [
            /** @var ?Database $db; */
            'db' => null,
            'parameters' => [
                'engine'       => 'sqlite',
                'host'         => '127.0.0.1',
                'user'         => '',
                'password'     => '',
                'database'     => __DIR__ . '/test_database.db',
                'report_error' => 'exception'
            ],
        ],
        'sqlite_silent' => [
            /** @var ?Database $db; */
            'db' => null,
            'parameters' => [
                'engine'        => 'sqlite',
                'host'          => '127.0.0.1',
                'user'          => '',
                'password'      => '',
                'database'      => __DIR__ . '/test_database.db',
                'report_error'  => 'silent'
            ],
        ]
    ];

    protected array $sqlQueries = [
        'mysql' => [
            'exec' => 'CREATE TABLE test_exec (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
            );',
            'insert' => 'CREATE TABLE test_insert (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
            );',
            'update' => [
                'CREATE TABLE test_update (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    name VARCHAR(255) NOT NULL,
                    PRIMARY KEY (id)
                );',
                "INSERT INTO test_update (name) VALUES ('A');",
                "INSERT INTO test_update (name) VALUES ('B');",
                "INSERT INTO test_update (name) VALUES ('C');",
            ],
            'delete' => [
                'CREATE TABLE test_delete (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    name VARCHAR(255) NOT NULL,
                    PRIMARY KEY (id)
                );',
                "INSERT INTO test_delete (name) VALUES ('A');",
                "INSERT INTO test_delete (name) VALUES ('B');",
                "INSERT INTO test_delete (name) VALUES ('C');",
            ],
        ],
        'pgsql' => [
            'exec' => 'CREATE TABLE test_exec (
                id SERIAL PRIMARY KEY,
                name character varying(255) NOT NULL
            );',
            'insert' => 'CREATE TABLE test_insert (
                id SERIAL PRIMARY KEY,
                name character varying(255) NOT NULL
            );',
            'update' => [
                'CREATE TABLE test_update (
                    id SERIAL PRIMARY KEY,
                    name character varying(255) NOT NULL
                );',
                "INSERT INTO test_update (name) VALUES ('A');",
                "INSERT INTO test_update (name) VALUES ('B');",
                "INSERT INTO test_update (name) VALUES ('C');",
            ],
            'delete' => [
                'CREATE TABLE test_delete (
                    id SERIAL PRIMARY KEY,
                    name character varying(255) NOT NULL
                );',
                "INSERT INTO test_delete (name) VALUES ('A');",
                "INSERT INTO test_delete (name) VALUES ('B');",
                "INSERT INTO test_delete (name) VALUES ('C');",
            ],
        ],
        'sqlite' => [
            'exec' => 'CREATE TABLE test_exec (
                id   INTEGER       PRIMARY KEY AUTOINCREMENT,
                name VARCHAR (255) NOT NULL
            );',
            'insert' => 'CREATE TABLE test_insert (
                id   INTEGER       PRIMARY KEY AUTOINCREMENT,
                name VARCHAR (255) NOT NULL
            );',
            'update' => [
                'CREATE TABLE test_update (
                    id   INTEGER       PRIMARY KEY AUTOINCREMENT,
                    name VARCHAR (255) NOT NULL
                );',
                "INSERT INTO test_update (name) VALUES ('A');",
                "INSERT INTO test_update (name) VALUES ('B');",
                "INSERT INTO test_update (name) VALUES ('C');",
            ],
            'delete' => [
                'CREATE TABLE test_delete (
                    id   INTEGER       PRIMARY KEY AUTOINCREMENT,
                    name VARCHAR (255) NOT NULL
                );',
                "INSERT INTO test_delete (name) VALUES ('A');",
                "INSERT INTO test_delete (name) VALUES ('B');",
                "INSERT INTO test_delete (name) VALUES ('C');",
            ],
        ],
    ];

    protected array $sqlFiles = [
        'mysql' => [__DIR__ . '/test-dump-mysql.sql'],
        'pgsql' => [__DIR__ . '/test-dump-pgsql-create-table.sql', __DIR__ . '/test-dump-pgsql-insert-table.sql'],
        'sqlite' => [__DIR__ . '/test-dump-sqlite.sql'],
    ];

    // region Data Provider

    public function sgbds(): array
    {
        return [
            'mysql_exception' => ['mysql_exception'],
            'mysql_silent' => ['mysql_silent'],
            'postgresql_exception' => ['postgresql_exception'],
            'sqlite_exception' => ['sqlite_exception'],
            'sqlite_silent' => ['sqlite_silent']
        ];
    }

    public function sgbdsException(): array
    {
        return [
            'mysql_exception' => ['mysql_exception'],
            'postgresql_exception' => ['postgresql_exception'],
            'sqlite_exception' => ['sqlite_exception']
        ];
    }

    public function sgbdsSilent(): array
    {
        return [
            'mysql_silent' => ['mysql_silent'],
            'sqlite_silent' => ['sqlite_silent']
        ];
    }

    public function sgbdsMysqlException(): array
    {
        return [
            'mysql_exception' => ['mysql_exception']
        ];
    }

    public function sgbdsMysqlSilent(): array
    {
        return [
            'mysql_silent' => ['mysql_silent']
        ];
    }

    public function sgbdsPostgresqlException(): array
    {
        return [
            'postgresql_exception' => ['postgresql_exception']
        ];
    }

    public function sgbdsSqliteException(): array
    {
        return [
            'sqlite_exception' => ['sqlite_exception']
        ];
    }

    public function sgbdsSqliteSilent(): array
    {
        return [
            'sqlite_silent' => ['sqlite_silent']
        ];
    }

    // endregion

    // region Setup / Teardown

    /**
     * @throws DatabaseException
     */
    public function setUp(): void
    {
        foreach ($this->sgbds as $k => $sgbd) {
            $configurator = new Configurator($this->sgbds[$k]['parameters']);
            $this->sgbds[$k]['db'] = new Database(new Configurator($this->sgbds[$k]['parameters']));
            $pdo = $configurator->createPDOConnection();

            $pdo->exec('DROP TABLE IF EXISTS test_exec');
            $pdo->exec('DROP TABLE IF EXISTS test_insert');
            $pdo->exec('DROP TABLE IF EXISTS test_update');
            $pdo->exec('DROP TABLE IF EXISTS test_delete');
            $pdo->exec('DROP TABLE IF EXISTS test_select');
        }
    }

    public function tearDown(): void
    {
        foreach ($this->sgbds as $k => $sgbd) {
            $this->sgbds[$k]['db']->disconnect();
            $this->sgbds[$k]['db'] = null;
        }
    }

    // endregion

    // region Database->Exec

    /**
     * @dataProvider sgbds
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testExec(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];
        $sql = $this->sqlQueries[$this->sgbds[$sgbd]['parameters']['engine']]['exec'];

        try {
            $success = $db->exec($sql);
            static::assertTrue($success);
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    /**
     * @dataProvider sgbdsException
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testExecException(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $this->expectException(DatabaseException::class);

        $db->exec('aaa');
    }

    /**
     * @dataProvider sgbdsSilent
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testExecError(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        try {
            $success = $db->exec('aaa');
            static::assertFalse($success);
            static::assertTrue($db->hasErrors());
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    // endregion

    // region Database->Insert

    /**
     * @dataProvider sgbds
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testInsert(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];
        $sql = $this->sqlQueries[$this->sgbds[$sgbd]['parameters']['engine']]['insert'];
        $db->exec($sql);

        try {
            $sql = "INSERT INTO test_insert (name) VALUES ('A')";
            $id = $db->insert($sql);
            static::assertTrue($id);

            $count = $db->count("SELECT COUNT(*) FROM test_insert WHERE name='A' AND id=1");
            static::assertSame(1, $count);

            $sql = 'INSERT INTO test_insert (name) VALUES (:name)';
            $params = ['name' => 'B'];
            $id = $db->insert($sql, $params);
            static::assertTrue($id);

            $count = $db->count("SELECT COUNT(*) FROM test_insert WHERE name='B' AND id=2");
            static::assertSame(1, $count);

            $params = ['name' => 'C'];
            $getLastInsertId = true;
            $id = $db->insert($sql, $params, $getLastInsertId);
            static::assertSame(3, $id);

            $count = $db->count("SELECT COUNT(*) FROM test_insert WHERE name='C' AND id=3");
            static::assertSame(1, $count);
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    /**
     * @dataProvider sgbdsException
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testInsertException(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $this->expectException(DatabaseException::class);

        $db->insert('aaa');
    }

    /**
     * @dataProvider sgbdsSilent
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testInsertError(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        try {
            $success = $db->insert('aaa');
            static::assertFalse($success);
            static::assertTrue($db->hasErrors());
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    // endregion

    // region Database->Update

    /**
     * @dataProvider sgbds
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testUpdate(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];
        $sqls = $this->sqlQueries[$this->sgbds[$sgbd]['parameters']['engine']]['update'];
        foreach ($sqls as $sql) {
            $db->exec($sql);
        }

        try {
            $sql = "UPDATE test_update SET name = 'AA' WHERE id = 1";
            $rowsAffected = $db->update($sql);
            static::assertTrue($rowsAffected);

            $count = $db->count("SELECT COUNT(*) FROM test_update WHERE name='AA' AND id=1");
            static::assertSame(1, $count);

            $sql = "UPDATE test_update SET name = :name WHERE id = :id";
            $params = ['id' => 2, 'name' => 'BB'];
            $rowsAffected = $db->update($sql, $params);
            static::assertTrue($rowsAffected);

            $count = $db->count("SELECT COUNT(*) FROM test_update WHERE name='BB' AND id=2");
            static::assertSame(1, $count);

            $params = ['id' => 3, 'name' => 'CC'];
            $getCountRowsAffected = true;
            $rowsAffected = $db->update($sql, $params, $getCountRowsAffected);
            static::assertSame(1, $rowsAffected);

            $count = $db->count("SELECT COUNT(*) FROM test_update WHERE name='CC' AND id=3");
            static::assertSame(1, $count);
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    /**
     * @dataProvider sgbdsException
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testUpdateException(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $this->expectException(DatabaseException::class);

        $db->update('aaa');
    }

    /**
     * @dataProvider sgbdsSilent
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testUpdateError(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        try {
            $success = $db->update('aaa');
            static::assertFalse($success);
            static::assertTrue($db->hasErrors());
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    // endregion

    // region Database->Delete

    /**
     * @dataProvider sgbds
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testDelete(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];
        $sqls = $this->sqlQueries[$this->sgbds[$sgbd]['parameters']['engine']]['delete'];
        foreach ($sqls as $sql) {
            $db->exec($sql);
        }

        try {
            $sql = 'DELETE FROM test_delete WHERE id = 1';
            $rowsAffected = $db->delete($sql);
            static::assertTrue($rowsAffected);

            $count = $db->count('SELECT COUNT(*) FROM test_delete WHERE id = 1');
            static::assertSame(0, $count);

            $sql = 'DELETE FROM test_delete WHERE id = :id';
            $params = ['id' => 2];
            $rowsAffected = $db->delete($sql, $params);
            static::assertTrue($rowsAffected);

            $count = $db->count('SELECT COUNT(*) FROM test_delete WHERE id = 2');
            static::assertSame(0, $count);

            $params = ['id' => 3];
            $getCountRowsAffected = true;
            $rowsAffected = $db->delete($sql, $params, $getCountRowsAffected);
            static::assertSame(1, $rowsAffected);

            $count = $db->count('SELECT COUNT(*) FROM test_delete WHERE id = 3');
            static::assertSame(0, $count);
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    /**
     * @dataProvider sgbdsException
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testDeleteException(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $this->expectException(DatabaseException::class);

        $db->delete('aaa');
    }

    /**
     * @dataProvider sgbdsSilent
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testDeleteError(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        try {
            $success = $db->delete('aaa');
            static::assertFalse($success);
            static::assertTrue($db->hasErrors());
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    // endregion

    // region Database->useSqlFile

    /**
     * @dataProvider sgbds
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testUseSqlFile(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];
        $sqlFiles = $this->sqlFiles[$this->sgbds[$sgbd]['parameters']['engine']];

        try {
            foreach ($sqlFiles as $sqlFile) {
                $success = $db->useSqlFile($sqlFile);
                static::assertTrue($success);
            }

            static::assertSame(6, $db->count('SELECT COUNT(*) FROM test_select'));
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    /**
     * @dataProvider sgbds
     * @param string $sgbd
     */
    public function testUseSqlFileExceptionMissingFile(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $this->expectException(DatabaseException::class);

        $db->useSqlFile('./missing-dump.sql');
    }

    /**
     * @dataProvider sgbdsException
     * @param string $sgbd
     */
    public function testUseSqlFileException(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $this->expectException(DatabaseException::class);

        $db->useSqlFile(__DIR__ . '/DatabaseTest.php');
    }

    /**
     * @dataProvider sgbdsSilent
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testUseSqlFileError(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        try {
            $success = $db->useSqlFile(__DIR__ . '/DatabaseTest.php');
            static::assertFalse($success);
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    // endregion

    // region Database->Connect

    /**
     * @dataProvider sgbds
     * @param string $sgbd
     * @throws DatabaseException
     * @noinspection GetClassUsageInspection
     */
    public function testConnect(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        try {
            static::assertNull($db->getPdo());

            $success = $db->connect();

            static::assertTrue($success);
            static::assertSame('PDO', get_class($db->getPdo()));
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    /**
     * @dataProvider sgbdsException
     * @param string $sgbd
     */
    public function testConnectException(string $sgbd): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Error Connecting Database');

        $params = $this->sgbds[$sgbd]['parameters'];
        $params['database'] = '/';
        $databaseConf = new Configurator($params);
        $db = new Database($databaseConf);
        $db->connect();
    }

    /**
     * @dataProvider sgbdsSilent
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testConnectError(string $sgbd): void
    {
        try {
            $params = $this->sgbds[$sgbd]['parameters'];
            $params['database'] = '/';
            $databaseConf = new Configurator($params);
            $db = new Database($databaseConf);
            $success = $db->connect();

            static::assertFalse($success);
            static::assertTrue($db->hasErrors());
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    // endregion

    // region Database->GetPdo

    /**
     * @dataProvider sgbds
     * @param string $sgbd
     * @throws DatabaseException
     * @noinspection GetClassUsageInspection
     */
    public function testGetPdo(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        try {
            static::assertNull($db->getPdo());

            $db->connect();

            static::assertSame('PDO', get_class($db->getPdo()));
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    // endregion

    // region Database->Disconnect

    /**
     * @dataProvider sgbds
     * @param string $sgbd
     * @throws DatabaseException
     * @noinspection GetClassUsageInspection
     */
    public function testDisconnect(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        try {
            $db->connect();

            static::assertSame('PDO', get_class($db->getPdo()));

            $db->disconnect();

            static::assertNull($db->getPdo());
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    // endregion
}
