<?php
/** @noinspection ForgottenDebugOutputInspection */
/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection SqlDialectInspection */

declare(strict_types=1);

namespace Rancoud\Database\Test;

use PHPUnit\Framework\TestCase;
use Rancoud\Database\Configurator;
use Rancoud\Database\Database;
use Rancoud\Database\DatabaseException;

/**
 * Class DatabaseSqliteSilentTest.
 */
class DatabaseSqliteSilentTest extends TestCase
{
    /** @var Database|null */
    protected ?Database $db;

    protected array $params = [
        'engine' => 'sqlite',
        'host' => '127.0.0.1',
        'user' => '',
        'password' => '',
        'database' => __DIR__ . '/test_database.db',
        'report_error' => 'silent'
    ];

    protected array $data = [
        [
            'id'      => '1',
            'name'    => 'A',
            'rank'    => '0',
            'comment' => null,
        ],
        [
            'id'      => '2',
            'name'    => 'B',
            'rank'    => '10',
            'comment' => 'yes',
        ],
        [
            'id'      => '3',
            'name'    => 'C',
            'rank'    => '20',
            'comment' => 'maybe',
        ],
        [
            'id'      => '4',
            'name'    => 'D',
            'rank'    => '30',
            'comment' => 'no',
        ],
        [
            'id'      => '5',
            'name'    => 'E',
            'rank'    => '25',
            'comment' => null,
        ],
        [
            'id'      => '6',
            'name'    => 'F',
            'rank'    => '5',
            'comment' => null,
        ]
    ];

    /**
     * @throws DatabaseException
     */
    public function setUp(): void
    {
        $databaseConf = new Configurator($this->params);
        $this->db = new Database($databaseConf);
    }

    public function tearDown(): void
    {
        $this->db->disconnect();
        $this->db = null;
    }

    /**
     * @throws DatabaseException
     */
    public function testExec(): void
    {
        $sql = 'CREATE TABLE IF NOT EXISTS test (
                id   INTEGER       PRIMARY KEY AUTOINCREMENT,
                name VARCHAR (255) NOT NULL
            );';

        try {
            $this->db->exec($sql);
            static::assertFalse($this->db->hasErrors());
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testExecError(): void
    {
        try {
            $this->db->exec('aaa');
            static::assertTrue($this->db->hasErrors());
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    protected function setTestTableForInsert(): void
    {
        $this->db->exec('DROP TABLE IF EXISTS test');
        $this->db->exec('CREATE TABLE test (
                id   INTEGER       PRIMARY KEY AUTOINCREMENT,
                name VARCHAR (255) NOT NULL
            );');
    }

    /**
     * @throws DatabaseException
     */
    public function testInsert(): void
    {
        try {
            $this->setTestTableForInsert();

            $sql = 'INSERT INTO test (name) VALUES ("A")';
            $id = $this->db->insert($sql);
            static::assertTrue($id);

            $count = $this->db->count('SELECT COUNT(*) FROM `test` WHERE name="A" AND id=1');
            static::assertSame(1, $count);

            $sql = 'INSERT INTO test (name) VALUES (:name)';
            $params = ['name' => 'B'];
            $id = $this->db->insert($sql, $params);
            static::assertTrue($id);

            $count = $this->db->count('SELECT COUNT(*) FROM `test` WHERE name="B" AND id=2');
            static::assertSame(1, $count);

            $params = ['name' => 'C'];
            $getLastInsertId = true;
            $id = $this->db->insert($sql, $params, $getLastInsertId);
            static::assertSame(3, $id);

            $count = $this->db->count('SELECT COUNT(*) FROM `test` WHERE name="C" AND id=3');
            static::assertSame(1, $count);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testInsertError(): void
    {
        try {
            $sql = 'a :a';
            $success = $this->db->insert($sql);
            static::assertFalse($success);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    protected function setTestTableForUpdate(): void
    {
        $this->db->exec('DROP TABLE IF EXISTS test');
        $this->db->exec('CREATE TABLE test (
                id   INTEGER       PRIMARY KEY AUTOINCREMENT,
                name VARCHAR (255) NOT NULL
            );');
        $this->db->exec('INSERT INTO test (name) VALUES ("A");');
        $this->db->exec('INSERT INTO test (name) VALUES ("B");');
        $this->db->exec('INSERT INTO test (name) VALUES ("C");');
    }

    /**
     * @throws DatabaseException
     */
    public function testUpdate(): void
    {
        try {
            $this->setTestTableForUpdate();

            $sql = 'UPDATE test SET name = "AA" WHERE id = 1';
            $rowsAffected = $this->db->update($sql);
            static::assertTrue($rowsAffected);

            $count = $this->db->count('SELECT COUNT(*) FROM `test` WHERE name="AA" AND id=1');
            static::assertSame(1, $count);

            $sql = 'UPDATE test SET name = :name WHERE id = :id';
            $params = ['id' => 2, 'name' => 'BB'];
            $rowsAffected = $this->db->update($sql, $params);
            static::assertTrue($rowsAffected);

            $count = $this->db->count('SELECT COUNT(*) FROM `test` WHERE name="BB" AND id=2');
            static::assertSame(1, $count);

            $params = ['id' => 3, 'name' => 'CC'];
            $getCountRowsAffected = true;
            $rowsAffected = $this->db->update($sql, $params, $getCountRowsAffected);
            static::assertSame(1, $rowsAffected);

            $count = $this->db->count('SELECT COUNT(*) FROM `test` WHERE name="CC" AND id=3');
            static::assertSame(1, $count);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testUpdateError(): void
    {
        try {
            $sql = 'a :a';
            $success = $this->db->update($sql);
            static::assertFalse($success);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    protected function setTestTableForDelete(): void
    {
        $this->db->exec('DROP TABLE IF EXISTS test');
        $this->db->exec('CREATE TABLE test (
                id   INTEGER       PRIMARY KEY AUTOINCREMENT,
                name VARCHAR (255) NOT NULL
            );');
        $this->db->exec('INSERT INTO test (name) VALUES ("A");');
        $this->db->exec('INSERT INTO test (name) VALUES ("B");');
        $this->db->exec('INSERT INTO test (name) VALUES ("C");');
    }

    /**
     * @throws DatabaseException
     */
    public function testDelete(): void
    {
        try {
            $this->setTestTableForDelete();

            $sql = 'DELETE FROM `test` WHERE id = 1';
            $rowsAffected = $this->db->delete($sql);
            static::assertTrue($rowsAffected);

            $count = $this->db->count('SELECT COUNT(*) FROM `test` WHERE id=1');
            static::assertSame(0, $count);

            $sql = 'DELETE FROM `test` WHERE id = :id';
            $params = ['id' => 2];
            $rowsAffected = $this->db->delete($sql, $params);
            static::assertTrue($rowsAffected);

            $count = $this->db->count('SELECT COUNT(*) FROM `test` WHERE id=2');
            static::assertSame(0, $count);

            $params = ['id' => 3];
            $getCountRowsAffected = true;
            $rowsAffected = $this->db->delete($sql, $params, $getCountRowsAffected);
            static::assertSame(1, $rowsAffected);

            $count = $this->db->count('SELECT COUNT(*) FROM `test` WHERE id=3');
            static::assertSame(0, $count);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testDeleteError(): void
    {
        try {
            $sql = 'a :a';
            $success = $this->db->delete($sql);
            static::assertFalse($success);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testUseSqlFile(): void
    {
        try {
            $this->db->exec('DROP TABLE IF EXISTS test_select');

            $success = $this->db->useSqlFile(__DIR__ . '/test-dump-sqlite.sql');
            static::assertTrue($success);

            static::assertSame(6, $this->db->count('SELECT COUNT(*) FROM test_select'));
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    public function testUseSqlFileException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('File missing for useSqlFile method: ./missing-dump.sql');

        $this->db->useSqlFile('./missing-dump.sql');
    }

    /**
     * @throws DatabaseException
     * @noinspection PhpAssignmentInConditionInspection
     */
    public function testRead(): void
    {
        try {
            $res = [];

            $this->db->insert('INSERT INTO test (name) VALUES (:name)', ['name' => 'A'], true);
            $this->db->insert('INSERT INTO test (name) VALUES (:name)', ['name' => 'B'], true);
            $this->db->insert('INSERT INTO test (name) VALUES (:name)', ['name' => 'C'], true);

            $cursor = $this->db->select('SELECT * FROM test');
            while ($row = $this->db->read($cursor)) {
                $res[] = $row;
            }

            static::assertCount(3, $res);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testCount(): void
    {
        try {
            $count = $this->db->count('SELECT COUNT(*) FROM test');
            static::assertSame(3, $count);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testSelectAll(): void
    {
        try {
            $rows = $this->db->selectAll('SELECT * FROM test');
            static::assertCount(3, $rows);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testSelectRow(): void
    {
        try {
            $row = $this->db->selectRow('SELECT * FROM test WHERE id = :id', ['id' => 3]);
            static::assertSame('B', $row['name']);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testSelectCol(): void
    {
        try {
            $col = $this->db->selectCol('SELECT id FROM test');
            static::assertCount(3, $col);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testSelectVar(): void
    {
        try {
            $var = $this->db->selectVar('SELECT name FROM test WHERE id = :id', ['id' => 3]);
            static::assertSame('B', $var);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testGetError(): void
    {
        try {
            $this->db->selectVar('SELECT namebbb FROM test WHERE id = :id', ['id' => 3]);
            static::assertTrue($this->db->hasErrors());
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testTruncateTable(): void
    {
        try {
            $this->db->truncateTable('test');
            static::assertFalse($this->db->hasErrors());
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testTruncateTables(): void
    {
        try {
            $this->db->truncateTables(['test', 'test']);

            static::assertFalse($this->db->hasErrors());
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    public function testDisconnect(): void
    {
        $this->db->disconnect();

        static::assertNull($this->db->getPdo());
    }

    /**
     * @throws DatabaseException
     */
    public function testExecStatementFalse(): void
    {
        try {
            $sql = 'SELECT namebbb FROM test WHERE id = :id';
            $success = $this->db->exec($sql);
            static::assertFalse($success);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testInsertStatementFalse(): void
    {
        try {
            $sql = 'a :a';
            $success = $this->db->insert($sql);
            static::assertFalse($success);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testUpdateStatementFalse(): void
    {
        try {
            $sql = 'a :a';
            $success = $this->db->update($sql);
            static::assertFalse($success);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testDeleteStatementFalse(): void
    {
        try {
            $sql = 'a :a';
            $success = $this->db->delete($sql);
            static::assertFalse($success);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testCountStatementFalse(): void
    {
        try {
            $sql = 'a';
            $success = $this->db->count($sql);
            static::assertFalse($success);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testSelectAllStatementFalse(): void
    {
        try {
            $sql = 'a :a';
            $success = $this->db->selectAll($sql);
            static::assertFalse($success);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testSelectRowStatementFalse(): void
    {
        try {
            $sql = 'a';
            $success = $this->db->selectRow($sql);
            static::assertFalse($success);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testSelectColStatementFalse(): void
    {
        try {
            $sql = 'a :a';
            $success = $this->db->selectCol($sql);
            static::assertFalse($success);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }
}
