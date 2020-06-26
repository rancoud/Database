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
            'select' => [
                'CREATE TABLE `test_select` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `name` varchar(45) NOT NULL,
                  `ranking` tinyint(1) unsigned NOT NULL,
                  `comment` text,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;',
                "INSERT INTO test_select (id, name, ranking, comment) VALUES (1, 'A', 0, NULL);",
                "INSERT INTO test_select (id, name, ranking, comment) VALUES (2, 'B', 10, 'yes');",
                "INSERT INTO test_select (id, name, ranking, comment) VALUES (3, 'C', 20, 'maybe');",
                "INSERT INTO test_select (id, name, ranking, comment) VALUES (4, 'D', 30, 'no');",
                "INSERT INTO test_select (id, name, ranking, comment) VALUES (5, 'E', 25, NULL);",
                "INSERT INTO test_select (id, name, ranking, comment) VALUES (6, 'F', 5, NULL);",
            ],
            'truncate' => [
                'CREATE TABLE test_truncate1 (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    name VARCHAR(255) NOT NULL,
                    PRIMARY KEY (id)
                );',
                "INSERT INTO test_truncate1 (name) VALUES ('A');",
                "INSERT INTO test_truncate1 (name) VALUES ('B');",
                "INSERT INTO test_truncate1 (name) VALUES ('C');",
                'CREATE TABLE test_truncate2 (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    name VARCHAR(255) NOT NULL,
                    PRIMARY KEY (id)
                );',
                "INSERT INTO test_truncate2 (name) VALUES ('A');",
                "INSERT INTO test_truncate2 (name) VALUES ('B');",
                "INSERT INTO test_truncate2 (name) VALUES ('C');",
            ]
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
            'select' => [
                'CREATE TABLE test_select(
                  id  SERIAL PRIMARY KEY,
                  name           character varying(255)      NOT NULL,
                  ranking        INT       NOT NULL,
                  comment        TEXT
                );',
                "INSERT INTO test_select (id, name, ranking, comment) VALUES (1, 'A', 0, NULL);",
                "INSERT INTO test_select (id, name, ranking, comment) VALUES (2, 'B', 10, 'yes');",
                "INSERT INTO test_select (id, name, ranking, comment) VALUES (3, 'C', 20, 'maybe');",
                "INSERT INTO test_select (id, name, ranking, comment) VALUES (4, 'D', 30, 'no');",
                "INSERT INTO test_select (id, name, ranking, comment) VALUES (5, 'E', 25, NULL);",
                "INSERT INTO test_select (id, name, ranking, comment) VALUES (6, 'F', 5, NULL);",
            ],
            'truncate' => [
                'CREATE TABLE test_truncate1 (
                    id SERIAL PRIMARY KEY,
                    name character varying(255) NOT NULL
                );',
                "INSERT INTO test_truncate1 (name) VALUES ('A');",
                "INSERT INTO test_truncate1 (name) VALUES ('B');",
                "INSERT INTO test_truncate1 (name) VALUES ('C');",
                'CREATE TABLE test_truncate2 (
                    id SERIAL PRIMARY KEY,
                    name character varying(255) NOT NULL
                );',
                "INSERT INTO test_truncate2 (name) VALUES ('A');",
                "INSERT INTO test_truncate2 (name) VALUES ('B');",
                "INSERT INTO test_truncate2 (name) VALUES ('C');",
            ]
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
            'select' => [
                'CREATE TABLE test_select (
                    id      INT          PRIMARY KEY  NOT NULL,
                    name    VARCHAR (45) NOT NULL,
                    ranking INT (1)      NOT NULL,
                    comment TEXT
                );',
                "INSERT INTO test_select (id, name, ranking, comment) VALUES (1, 'A', 0, NULL);",
                "INSERT INTO test_select (id, name, ranking, comment) VALUES (2, 'B', 10, 'yes');",
                "INSERT INTO test_select (id, name, ranking, comment) VALUES (3, 'C', 20, 'maybe');",
                "INSERT INTO test_select (id, name, ranking, comment) VALUES (4, 'D', 30, 'no');",
                "INSERT INTO test_select (id, name, ranking, comment) VALUES (5, 'E', 25, NULL);",
                "INSERT INTO test_select (id, name, ranking, comment) VALUES (6, 'F', 5, NULL);",
            ],
            'truncate' => [
                'CREATE TABLE test_truncate1 (
                    id   INTEGER       PRIMARY KEY AUTOINCREMENT,
                    name VARCHAR (255) NOT NULL
                );',
                "INSERT INTO test_truncate1 (name) VALUES ('A');",
                "INSERT INTO test_truncate1 (name) VALUES ('B');",
                "INSERT INTO test_truncate1 (name) VALUES ('C');",
                'CREATE TABLE test_truncate2 (
                    id   INTEGER       PRIMARY KEY AUTOINCREMENT,
                    name VARCHAR (255) NOT NULL
                );',
                "INSERT INTO test_truncate2 (name) VALUES ('A');",
                "INSERT INTO test_truncate2 (name) VALUES ('B');",
                "INSERT INTO test_truncate2 (name) VALUES ('C');",
            ]
        ],
    ];

    protected array $selectData = [
        'mysql' => [
            [
                'id'      => '1',
                'name'    => 'A',
                'ranking' => '0',
                'comment' => null,
            ],
            [
                'id'      => '2',
                'name'    => 'B',
                'ranking' => '10',
                'comment' => 'yes',
            ],
            [
                'id'      => '3',
                'name'    => 'C',
                'ranking' => '20',
                'comment' => 'maybe',
            ],
            [
                'id'      => '4',
                'name'    => 'D',
                'ranking' => '30',
                'comment' => 'no',
            ],
            [
                'id'      => '5',
                'name'    => 'E',
                'ranking' => '25',
                'comment' => null,
            ],
            [
                'id'      => '6',
                'name'    => 'F',
                'ranking' => '5',
                'comment' => null,
            ]
        ],
        'pgsql' => [
            [
                'id'      => 1,
                'name'    => 'A',
                'ranking' => 0,
                'comment' => null,
            ],
            [
                'id'      => 2,
                'name'    => 'B',
                'ranking' => 10,
                'comment' => 'yes',
            ],
            [
                'id'      => 3,
                'name'    => 'C',
                'ranking' => 20,
                'comment' => 'maybe',
            ],
            [
                'id'      => 4,
                'name'    => 'D',
                'ranking' => 30,
                'comment' => 'no',
            ],
            [
                'id'      => 5,
                'name'    => 'E',
                'ranking' => 25,
                'comment' => null,
            ],
            [
                'id'      => 6,
                'name'    => 'F',
                'ranking' => 5,
                'comment' => null,
            ]
        ],
        'sqlite' => [
            [
                'id'      => '1',
                'name'    => 'A',
                'ranking' => '0',
                'comment' => null,
            ],
            [
                'id'      => '2',
                'name'    => 'B',
                'ranking' => '10',
                'comment' => 'yes',
            ],
            [
                'id'      => '3',
                'name'    => 'C',
                'ranking' => '20',
                'comment' => 'maybe',
            ],
            [
                'id'      => '4',
                'name'    => 'D',
                'ranking' => '30',
                'comment' => 'no',
            ],
            [
                'id'      => '5',
                'name'    => 'E',
                'ranking' => '25',
                'comment' => null,
            ],
            [
                'id'      => '6',
                'name'    => 'F',
                'ranking' => '5',
                'comment' => null,
            ]
        ]
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
            $pdo->exec('DROP TABLE IF EXISTS test_truncate1');
            $pdo->exec('DROP TABLE IF EXISTS test_truncate2');
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
            $params = ['name' => '💪'];
            $id = $db->insert($sql, $params);
            static::assertTrue($id);

            $count = $db->count("SELECT COUNT(*) FROM test_insert WHERE name='💪' AND id=2");
            static::assertSame(1, $count);

            static::assertSame('💪', $db->selectVar("SELECT name FROM test_insert WHERE id=2"));

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

    // region Database->SelectAll

    /**
     * @dataProvider sgbds
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testSelectAll(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $sqls = $this->sqlQueries[$this->sgbds[$sgbd]['parameters']['engine']]['select'];
        foreach ($sqls as $sql) {
            $db->exec($sql);
        }

        try {
            $sql = 'SELECT * FROM test_select';
            $rows = $db->selectAll($sql);
            static::assertSame($this->selectData[$this->sgbds[$sgbd]['parameters']['engine']], $rows);

            $sql = 'SELECT * FROM test_select WHERE ranking >= :ranking';
            $params = ['ranking' => 20];
            $rows = $db->selectAll($sql, $params);
            $data[] = $this->selectData[$this->sgbds[$sgbd]['parameters']['engine']][2];
            $data[] = $this->selectData[$this->sgbds[$sgbd]['parameters']['engine']][3];
            $data[] = $this->selectData[$this->sgbds[$sgbd]['parameters']['engine']][4];
            static::assertSame($data, $rows);

            $sql = 'SELECT * FROM test_select WHERE ranking >= :ranking';
            $params = ['ranking' => 100];
            $rows = $db->selectAll($sql, $params);
            static::assertSame([], $rows);
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
    public function testSelectAllException(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $this->expectException(DatabaseException::class);

        $db->selectAll('aaa');
    }

    /**
     * @dataProvider sgbdsSilent
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testSelectAllError(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        try {
            static::assertFalse($db->selectAll('aaa'));
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    // endregion

    // region Database->SelectRow

    /**
     * @dataProvider sgbds
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testSelectRow(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $sqls = $this->sqlQueries[$this->sgbds[$sgbd]['parameters']['engine']]['select'];
        foreach ($sqls as $sql) {
            $db->exec($sql);
        }

        try {
            $sql = 'SELECT * FROM test_select';
            $row = $db->selectRow($sql);
            static::assertSame($this->selectData[$this->sgbds[$sgbd]['parameters']['engine']][0], $row);

            $sql = 'SELECT * FROM test_select WHERE ranking >= :ranking';
            $params = ['ranking' => 20];
            $row = $db->selectRow($sql, $params);
            static::assertSame($this->selectData[$this->sgbds[$sgbd]['parameters']['engine']][2], $row);

            $sql = 'SELECT * FROM test_select WHERE ranking >= :ranking';
            $params = ['ranking' => 100];
            $rows = $db->selectRow($sql, $params);
            static::assertSame([], $rows);
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
    public function testSelectRowException(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $this->expectException(DatabaseException::class);

        $db->selectRow('aaa');
    }

    /**
     * @dataProvider sgbdsSilent
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testSelectRowError(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        try {
            static::assertFalse($db->selectRow('aaa'));
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    // endregion

    // region Database->SelectCol

    /**
     * @dataProvider sgbds
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testSelectCol(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $sqls = $this->sqlQueries[$this->sgbds[$sgbd]['parameters']['engine']]['select'];
        foreach ($sqls as $sql) {
            $db->exec($sql);
        }

        try {
            $sql = 'SELECT * FROM test_select';
            $col = $db->selectCol($sql);
            static::assertSame([
                $this->selectData[$this->sgbds[$sgbd]['parameters']['engine']][0]['id'],
                $this->selectData[$this->sgbds[$sgbd]['parameters']['engine']][1]['id'],
                $this->selectData[$this->sgbds[$sgbd]['parameters']['engine']][2]['id'],
                $this->selectData[$this->sgbds[$sgbd]['parameters']['engine']][3]['id'],
                $this->selectData[$this->sgbds[$sgbd]['parameters']['engine']][4]['id'],
                $this->selectData[$this->sgbds[$sgbd]['parameters']['engine']][5]['id']
            ], $col);

            $sql = 'SELECT name FROM test_select WHERE ranking >= :ranking';
            $params = ['ranking' => 20];
            $col = $db->selectCol($sql, $params);
            static::assertSame([
                $this->selectData[$this->sgbds[$sgbd]['parameters']['engine']][2]['name'],
                $this->selectData[$this->sgbds[$sgbd]['parameters']['engine']][3]['name'],
                $this->selectData[$this->sgbds[$sgbd]['parameters']['engine']][4]['name']
            ], $col);

            $sql = 'SELECT ranking FROM test_select WHERE ranking >= :ranking';
            $params = ['ranking' => 100];
            $col = $db->selectCol($sql, $params);
            static::assertSame([], $col);
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
    public function testSelectColException(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $this->expectException(DatabaseException::class);

        $db->selectCol('aaa');
    }

    /**
     * @dataProvider sgbdsSilent
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testSelectColError(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        try {
            static::assertFalse($db->selectCol('aaa'));
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    // endregion

    // region Database->SelectVar

    /**
     * @dataProvider sgbds
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testSelectVar(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $sqls = $this->sqlQueries[$this->sgbds[$sgbd]['parameters']['engine']]['select'];
        foreach ($sqls as $sql) {
            $db->exec($sql);
        }

        try {
            $sql = 'SELECT * FROM test_select';
            $var = $db->selectVar($sql);
            static::assertSame($this->selectData[$this->sgbds[$sgbd]['parameters']['engine']][0]['id'], $var);

            $sql = 'SELECT name FROM test_select WHERE ranking >= :ranking';
            $params = ['ranking' => 20];
            $var = $db->selectVar($sql, $params);
            static::assertSame($this->selectData[$this->sgbds[$sgbd]['parameters']['engine']][2]['name'], $var);

            $sql = 'SELECT ranking FROM test_select WHERE ranking >= :ranking';
            $params = ['ranking' => 100];
            $var = $db->selectVar($sql, $params);
            static::assertFalse($var);
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
    public function testSelectVarException(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $this->expectException(DatabaseException::class);

        $db->selectVar('aaa');
    }

    /**
     * @dataProvider sgbdsSilent
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testSelectVarError(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        try {
            static::assertFalse($db->selectVar('aaa'));
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    // endregion

    // region Database->Select

    /**
     * @dataProvider sgbds
     * @param string $sgbd
     * @throws DatabaseException
     * @noinspection GetClassUsageInspection
     */
    public function testSelect(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $sqls = $this->sqlQueries[$this->sgbds[$sgbd]['parameters']['engine']]['select'];
        foreach ($sqls as $sql) {
            $db->exec($sql);
        }

        try {
            $sql = 'SELECT * FROM test_select';
            $statement = $db->select($sql);
            static::assertSame(PDOStatement::class, get_class($statement));

            $sql = 'SELECT * FROM test_select WHERE ranking >= :ranking';
            $params = ['ranking' => 20];
            $statement = $db->select($sql, $params);
            static::assertSame(PDOStatement::class, get_class($statement));

            $sql = 'SELECT * FROM test_select WHERE ranking >= :ranking';
            $params = ['ranking' => 100];
            $statement = $db->select($sql, $params);
            static::assertSame(PDOStatement::class, get_class($statement));
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
    public function testSelectException(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $this->expectException(DatabaseException::class);

        $db->select('aaa');
    }

    /**
     * @dataProvider sgbdsSilent
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testSelectError(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        try {
            static::assertNull($db->select('aaa'));
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    // endregion

    // region Database->Read

    /**
     * @dataProvider sgbds
     * @param string $sgbd
     * @throws DatabaseException
     * @noinspection PhpAssignmentInConditionInspection
     */
    public function testRead(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $sqls = $this->sqlQueries[$this->sgbds[$sgbd]['parameters']['engine']]['select'];
        foreach ($sqls as $sql) {
            $db->exec($sql);
        }

        try {
            $sql = 'SELECT * FROM test_select';
            $statement = $db->select($sql);
            $rows = $db->read($statement);
            static::assertCount(4, $rows);

            $sql = 'SELECT * FROM test_select WHERE ranking >= :ranking';
            $params = ['ranking' => 20];
            $statement = $db->select($sql, $params);
            $rows = $db->read($statement);
            static::assertCount(4, $rows);

            $sql = 'SELECT * FROM test_select WHERE ranking >= :ranking';
            $params = ['ranking' => 100];
            $statement = $db->select($sql, $params);
            $row = $db->read($statement);
            static::assertFalse($row);

            $rows = [];
            $statement = $db->select('SELECT * FROM test_select');
            while ($row = $db->read($statement)) {
                $rows[] = $row;
            }

            static::assertCount(6, $rows);
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    // endregion

    // region Database->ReadAll

    /**
     * @dataProvider sgbds
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testReadAll(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $sqls = $this->sqlQueries[$this->sgbds[$sgbd]['parameters']['engine']]['select'];
        foreach ($sqls as $sql) {
            $db->exec($sql);
        }

        try {
            $sql = 'SELECT * FROM test_select';
            $statement = $db->select($sql);
            $rows = $db->readAll($statement);
            static::assertCount(6, $rows);

            $sql = 'SELECT * FROM test_select WHERE ranking >= :ranking';
            $params = ['ranking' => 20];
            $statement = $db->select($sql, $params);
            $rows = $db->readAll($statement);
            static::assertCount(3, $rows);

            $sql = 'SELECT * FROM test_select WHERE ranking >= :ranking';
            $params = ['ranking' => 100];
            $statement = $db->select($sql, $params);
            $rows = $db->readAll($statement);
            static::assertCount(0, $rows);
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    // endregion

    // region Database->Count

    /**
     * @dataProvider sgbds
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testCount(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];
        $sqls = $this->sqlQueries[$this->sgbds[$sgbd]['parameters']['engine']]['select'];
        foreach ($sqls as $sql) {
            $db->exec($sql);
        }

        try {
            static::assertSame(6, $db->count('SELECT COUNT(*) from test_select'));
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
    public function testCountException(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $this->expectException(DatabaseException::class);

        $db->count('aaa');
    }

    /**
     * @dataProvider sgbdsSilent
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testCountError(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        try {
            $success = $db->count('aaa');
            static::assertFalse($success);
            static::assertTrue($db->hasErrors());
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    // endregion

    // region Database Pdo Param

    /**
     * @dataProvider sgbds
     * @param string $sgbd
     * @throws DatabaseException
     * @noinspection FopenBinaryUnsafeUsageInspection
     */
    public function testPdoParamType(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        try {
            if ($this->sgbds[$sgbd]['parameters']['engine'] !== 'pgsql') {
                $sql = 'SELECT :true AS `true`, :false AS `false`, :null AS `null`, :float AS `float`,
                :int AS `int`, :string AS `string`, :resource AS `resource`';
                $params = [
                    'true' => true,
                    'false' => false,
                    'null' => null,
                    'float' => 1.2,
                    'int' => 800,
                    'string' => 'string',
                    'resource' => fopen(__DIR__ . '/test-dump-mysql.sql', 'r')
                ];

                $row = $db->selectRow($sql, $params);

                static::assertSame('1', $row['true']);
                static::assertSame('0', $row['false']);
                static::assertNull($row['null']);
                static::assertSame('1.2', $row['float']);
                static::assertSame('800', $row['int']);
                static::assertSame('string', $row['string']);
                static::assertSame('-- MySQL dump', mb_substr($row['resource'], 0, 13));
            } else {
                $sql = 'SELECT :true AS true, :false AS false, :null AS null, :float AS float,
                :int AS int, :string AS string, :resource AS resource';
                $params = [
                    'true' => true,
                    'false' => false,
                    'null' => null,
                    'float' => 1.2,
                    'int' => 800,
                    'string' => 'string',
                    'resource' => fopen(__DIR__ . '/test-dump-mysql.sql', 'r')
                ];
                $row = $db->selectRow($sql, $params);

                static::assertSame('t', $row['true']);
                static::assertSame('f', $row['false']);
                static::assertNull($row['null']);
                static::assertSame('1.2', $row['float']);
                static::assertSame('800', $row['int']);
                static::assertSame('string', $row['string']);
                static::assertSame('-- MySQL dump', mb_substr($row['resource'], 0, 13));
            }
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
    public function testPdoParamTypeException(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $this->expectException(DatabaseException::class);

        $sql = 'SELECT :array AS array';
        $params = ['array' => []];
        $db->selectRow($sql, $params);
    }

    /**
     * @dataProvider sgbdsSilent
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testPdoParamTypeError(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        try {
            $sql = 'SELECT :array AS array';
            $params = ['array' => []];
            static::assertFalse($db->selectRow($sql, $params));
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    // endregion

    // region Database startTransaction/commitTransaction/rollbackTransaction/completeTransaction

    /**
     * @dataProvider sgbds
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testCommitTransaction(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $sqls = $this->sqlQueries[$this->sgbds[$sgbd]['parameters']['engine']]['select'];
        foreach ($sqls as $sql) {
            $db->exec($sql);
        }

        try {
            static::assertFalse($db->completeTransaction());

            $success = $db->commitTransaction();
            static::assertFalse($success);

            $success = $db->startTransaction();
            static::assertTrue($success);

            $sql = 'UPDATE test_select SET name = :name WHERE id =:id';
            $params = ['name' => 'my name', 'id' => 1];
            $db->update($sql, $params);

            $success = $db->commitTransaction();
            static::assertTrue($success);
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }

        $sql = 'SELECT name FROM test_select WHERE id = :id';
        $params = ['id' => 1];
        static::assertSame('my name', $db->selectVar($sql, $params));
    }

    /**
     * @dataProvider sgbds
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testRollbackTransaction(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $sqls = $this->sqlQueries[$this->sgbds[$sgbd]['parameters']['engine']]['select'];
        foreach ($sqls as $sql) {
            $db->exec($sql);
        }

        try {
            static::assertFalse($db->rollbackTransaction());

            $success = $db->startTransaction();
            static::assertTrue($success);

            $sql = 'UPDATE test_select SET name = :name WHERE id =:id';
            $params = ['name' => 'my name', 'id' => 1];
            $db->update($sql, $params);

            $sql = 'SELECT name FROM test_select WHERE id = :id';
            $params = ['id' => 1];
            static::assertSame('my name', $db->selectVar($sql, $params));

            $success = $db->rollbackTransaction();
            static::assertTrue($success);

            $sql = 'SELECT name FROM test_select WHERE id = :id';
            $params = ['id' => 1];
            static::assertSame('A', $db->selectVar($sql, $params));
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }

        $sql = 'SELECT name FROM test_select WHERE id = :id';
        $params = ['id' => 1];
        static::assertSame('A', $db->selectVar($sql, $params));
    }

    /**
     * @dataProvider sgbds
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testCompleteTransaction(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $sqls = $this->sqlQueries[$this->sgbds[$sgbd]['parameters']['engine']]['select'];
        foreach ($sqls as $sql) {
            $db->exec($sql);
        }

        try {
            static::assertFalse($db->completeTransaction());

            $success = $db->startTransaction();
            static::assertTrue($success);

            $sql = 'UPDATE test_select SET name = :name WHERE id =:id';
            $params = ['name' => 'my name', 'id' => 1];
            $db->update($sql, $params);

            $success = $db->completeTransaction();
            static::assertTrue($success);
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }

        $sql = 'SELECT name FROM test_select WHERE id = :id';
        $params = ['id' => 1];
        static::assertSame('my name', $db->selectVar($sql, $params));
    }

    /**
     * @dataProvider sgbdsSilent
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testCompleteTransactionAutoRollbackForSilent(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $sqls = $this->sqlQueries[$this->sgbds[$sgbd]['parameters']['engine']]['select'];
        foreach ($sqls as $sql) {
            $db->exec($sql);
        }

        try {
            static::assertFalse($db->completeTransaction());

            $success = $db->startTransaction();
            static::assertTrue($success);

            $sql = 'UPDATE test_select SET name = :name WHERE id =:id';
            $params = ['name' => 'my name', 'id' => 1];
            $db->update($sql, $params);

            $success = $db->selectAll('aaa');
            static::assertFalse($success);

            $success = $db->completeTransaction();
            static::assertFalse($success);
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }

        $sql = 'SELECT name FROM test_select WHERE id = :id';
        $params = ['id' => 1];
        static::assertSame('A', $db->selectVar($sql, $params));
    }

    /**
     * @dataProvider sgbds
     * @param string $sgbd
     */
    public function testInTransactionError(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $success = $db->completeTransaction();
        static::assertFalse($success);

        $success = $db->commitTransaction();
        static::assertFalse($success);

        $success = $db->rollbackTransaction();
        static::assertFalse($success);
    }

    /**
     * @dataProvider sgbds
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testStartCommitAutoConnect(string $sgbd): void
    {
        $db = new Database(new Configurator($this->sgbds[$sgbd]['parameters']));
        static::assertNull($db->getPdo());
        static::assertTrue($db->startTransaction());
        static::assertNotNull($db->getPdo());
    }

    // endregion

    // region Database hasErrors/getErrors/getLastError/cleanErrors

    /**
     * @dataProvider sgbdsException
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testErrorsException(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $sqls = $this->sqlQueries[$this->sgbds[$sgbd]['parameters']['engine']]['select'];
        foreach ($sqls as $sql) {
            $db->exec($sql);
        }

        static::assertFalse($db->hasErrors());
        static::assertSame([], $db->getErrors());
        static::assertNull($db->getLastError());

        try {
            $db->select('aaa');
            // if assert is done then it's not good
            static::assertFalse(true);
        } catch (DatabaseException $e) {
            static::assertTrue($db->hasErrors());
            static::assertCount(4, $db->getLastError());

            $db->cleanErrors();

            static::assertFalse($db->hasErrors());
            static::assertSame([], $db->getErrors());
            static::assertNull($db->getLastError());
        }
    }

    /**
     * @dataProvider sgbdsSilent
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testErrorsError(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $sqls = $this->sqlQueries[$this->sgbds[$sgbd]['parameters']['engine']]['select'];
        foreach ($sqls as $sql) {
            $db->exec($sql);
        }

        static::assertFalse($db->hasErrors());
        static::assertSame([], $db->getErrors());
        static::assertNull($db->getLastError());

        try {
            $statement = $db->select('aaa');
            static::assertNull($statement);
            static::assertTrue($db->hasErrors());
            static::assertCount(4, $db->getLastError());

            $db->cleanErrors();

            static::assertFalse($db->hasErrors());
            static::assertSame([], $db->getErrors());
            static::assertNull($db->getLastError());
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    // endregion

    // region Database hasSaveQueries/getSavedQueries/cleanSavedQueries

    /**
     * @dataProvider sgbds
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testSaveQueries(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $sqls = $this->sqlQueries[$this->sgbds[$sgbd]['parameters']['engine']]['select'];
        foreach ($sqls as $sql) {
            $db->exec($sql);
        }

        try {
            static::assertFalse($db->hasSaveQueries());

            $db->enableSaveQueries();

            static::assertTrue($db->hasSaveQueries());

            $db->disableSaveQueries();

            static::assertFalse($db->hasSaveQueries());

            $db->enableSaveQueries();

            static::assertTrue($db->hasSaveQueries());

            static::assertSame([], $db->getSavedQueries());

            $db->selectAll('SELECT * FROM test_select');

            $queries = $db->getSavedQueries();

            static::assertCount(1, $queries);

            $db->cleanSavedQueries();

            $queries = $db->getSavedQueries();

            static::assertCount(0, $queries);
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

    // region Database->TruncateTables

    /**
     * @dataProvider sgbds
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testTruncateTables(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $sqls = $this->sqlQueries[$this->sgbds[$sgbd]['parameters']['engine']]['truncate'];
        foreach ($sqls as $sql) {
            $db->exec($sql);
        }

        try {
            static::assertSame(3, $db->count('SELECT COUNT(*) FROM test_truncate1'));
            static::assertSame(3, $db->count('SELECT COUNT(*) FROM test_truncate2'));
            static::assertTrue($db->truncateTables('test_truncate1', 'test_truncate2'));
            static::assertSame(0, $db->count('SELECT COUNT(*) FROM test_truncate1'));
            static::assertSame(0, $db->count('SELECT COUNT(*) FROM test_truncate2'));
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
    public function testTruncateTablesException(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $this->expectException(DatabaseException::class);

        $db->truncateTables('');
    }

    /**
     * @dataProvider sgbdsSilent
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testTruncateTablesError(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        try {
            $success = $db->truncateTables('');
            static::assertFalse($success);
            static::assertTrue($db->hasErrors());
        } catch (DatabaseException $e) {
            var_dump($db->getErrors());
            throw $e;
        }
    }

    // endregion

    // region Database->DropTables

    /**
     * @dataProvider sgbds
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testDropTables(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $sqls = $this->sqlQueries[$this->sgbds[$sgbd]['parameters']['engine']]['truncate'];
        foreach ($sqls as $sql) {
            $db->exec($sql);
        }

        try {
            if ($this->sgbds[$sgbd]['parameters']['engine'] === 'mysql') {
                $sql1 = "SELECT COUNT(*) FROM information_schema.tables
                         WHERE table_schema = 'test_database' AND table_name = 'test_truncate1';";
                $sql2 = "SELECT COUNT(*) FROM information_schema.tables
                         WHERE table_schema = 'test_database' AND table_name = 'test_truncate2';";

                static::assertSame(1, $db->count($sql1));
                static::assertSame(1, $db->count($sql2));
                static::assertTrue($db->dropTables('test_truncate1', 'test_truncate2'));
                static::assertSame(0, $db->count($sql1));
                static::assertSame(0, $db->count($sql2));
            } elseif ($this->sgbds[$sgbd]['parameters']['engine'] === 'pgsql') {
                static::assertSame('test_truncate1', $db->selectVar("SELECT to_regclass('test_truncate1');"));
                static::assertSame('test_truncate2', $db->selectVar("SELECT to_regclass('test_truncate2');"));
                static::assertTrue($db->dropTables('test_truncate1', 'test_truncate2'));
                static::assertNull($db->selectVar("SELECT to_regclass('test_truncate2');"));
                static::assertNull($db->selectVar("SELECT to_regclass('test_truncate2');"));
            } elseif ($this->sgbds[$sgbd]['parameters']['engine'] === 'sqlite') {
                $sql1 = "SELECT count(*) FROM sqlite_master WHERE type='table' AND name='test_truncate1';";
                $sql2 = "SELECT count(*) FROM sqlite_master WHERE type='table' AND name='test_truncate2';";
                static::assertSame(1, $db->count($sql1));
                static::assertSame(1, $db->count($sql2));
                static::assertTrue($db->dropTables('test_truncate1', 'test_truncate2'));
                static::assertSame(0, $db->count($sql1));
                static::assertSame(0, $db->count($sql2));
            } else {
                throw new DatabaseException('sgbd ' . $sgbd . ' not supported!');
            }
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
    public function testDropTablesException(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        $this->expectException(DatabaseException::class);

        $db->dropTables('');
    }

    /**
     * @dataProvider sgbdsSilent
     * @param string $sgbd
     * @throws DatabaseException
     */
    public function testDropTablesError(string $sgbd): void
    {
        /** @var Database $db */
        $db = $this->sgbds[$sgbd]['db'];

        try {
            $success = $db->dropTables('');
            static::assertFalse($success);
            static::assertTrue($db->hasErrors());
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
