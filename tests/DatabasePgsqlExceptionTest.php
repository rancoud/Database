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
 * Class DatabasePgsqlExceptionTest.
 */
class DatabasePgsqlExceptionTest extends TestCase
{
    /** @var Database|null */
    protected ?Database $db;

    protected array $params = [
        'engine'       => 'pgsql',
        'host'         => '127.0.0.1',
        'user'         => 'postgres',
        'password'     => '',
        'database'     => 'test_database',
        'report_error' => 'exception'
    ];

    protected array $data = [
        [
            'id'      => 1,
            'name'    => 'A',
            'rank'    => 0,
            'comment' => null,
        ],
        [
            'id'      => 2,
            'name'    => 'B',
            'rank'    => 10,
            'comment' => 'yes',
        ],
        [
            'id'      => 3,
            'name'    => 'C',
            'rank'    => 20,
            'comment' => 'maybe',
        ],
        [
            'id'      => 4,
            'name'    => 'D',
            'rank'    => 30,
            'comment' => 'no',
        ],
        [
            'id'      => 5,
            'name'    => 'E',
            'rank'    => 25,
            'comment' => null,
        ],
        [
            'id'      => 6,
            'name'    => 'F',
            'rank'    => 5,
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
    public function testFirstLaunch(): void
    {
        try {
            $success = $this->db->dropTables(['test', 'test_select']);
            static::assertTrue($success);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testExec(): void
    {
        try {
            $success = $this->db->exec('CREATE TABLE test (
    id SERIAL PRIMARY KEY,
    name character varying(255) NOT NULL
);');

            static::assertTrue($success);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    public function testExecException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Error Execute');

        $this->db->exec('aaa');
    }

    /**
     * @throws DatabaseException
     */
    public function testInsert(): void
    {
        try {
            $sql = "INSERT INTO test (name) VALUES ('A')";
            $id = $this->db->insert($sql);
            static::assertTrue($id);

            $count = $this->db->count("SELECT COUNT(*) FROM test WHERE name='A' AND id=1");
            static::assertSame(1, $count);

            $sql = 'INSERT INTO test (name) VALUES (:name)';
            $params = ['name' => 'B'];
            $id = $this->db->insert($sql, $params);
            static::assertTrue($id);

            $count = $this->db->count("SELECT COUNT(*) FROM test WHERE name='B' AND id=2");
            static::assertSame(1, $count);

            $params = ['name' => 'C'];
            $getLastInsertId = true;
            $id = $this->db->insert($sql, $params, $getLastInsertId);
            static::assertSame(3, $id);

            $count = $this->db->count("SELECT COUNT(*) FROM test WHERE name='C' AND id=3");
            static::assertSame(1, $count);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    public function testInsertException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Error Execute');

        $sql = 'INSERT INTO test (name) VALUES (:name)';

        $this->db->insert($sql);
    }

    /**
     * @throws DatabaseException
     */
    public function testUpdate(): void
    {
        try {
            $sql = "UPDATE test SET name = 'AA' WHERE id = 1";
            $rowsAffected = $this->db->update($sql);
            static::assertTrue($rowsAffected);

            $count = $this->db->count("SELECT COUNT(*) FROM test WHERE name='AA' AND id=1");
            static::assertSame(1, $count);

            $sql = 'UPDATE test SET name = :name WHERE id = :id';
            $params = ['id' => 2, 'name' => 'BB'];
            $rowsAffected = $this->db->update($sql, $params);
            static::assertTrue($rowsAffected);

            $count = $this->db->count("SELECT COUNT(*) FROM test WHERE name='BB' AND id=2");
            static::assertSame(1, $count);

            $params = ['id' => 3, 'name' => 'CC'];
            $getCountRowsAffected = true;
            $rowsAffected = $this->db->update($sql, $params, $getCountRowsAffected);
            static::assertSame(1, $rowsAffected);

            $count = $this->db->count("SELECT COUNT(*) FROM test WHERE name='CC' AND id=3");
            static::assertSame(1, $count);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    public function testUpdateException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Error Execute');

        $sql = 'UPDATE test SET name = :name WHERE id = :id';

        $this->db->update($sql);
    }

    /**
     * @throws DatabaseException
     */
    public function testDelete(): void
    {
        try {
            $sql = 'DELETE FROM test WHERE id = 1';
            $rowsAffected = $this->db->delete($sql);
            static::assertTrue($rowsAffected);

            $count = $this->db->count('SELECT COUNT(*) FROM test WHERE id=1');
            static::assertSame(0, $count);

            $sql = 'DELETE FROM test WHERE id = :id';
            $params = ['id' => 2];
            $rowsAffected = $this->db->delete($sql, $params);
            static::assertTrue($rowsAffected);

            $count = $this->db->count('SELECT COUNT(*) FROM test WHERE id=2');
            static::assertSame(0, $count);

            $params = ['id' => 3];
            $getCountRowsAffected = true;
            $rowsAffected = $this->db->delete($sql, $params, $getCountRowsAffected);
            static::assertSame(1, $rowsAffected);

            $count = $this->db->count('SELECT COUNT(*) FROM test WHERE id=3');
            static::assertSame(0, $count);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    public function testDeleteException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Error Execute');

        $sql = 'DELETE FROM test WHERE id = :id';

        $this->db->delete($sql);
    }

    /**
     * @throws DatabaseException
     */
    public function testUseSqlFile(): void
    {
        try {
            $success = $this->db->useSqlFile(__DIR__ . '/test-dump-pgsql-create-table.sql');
            static::assertTrue($success);

            $success = $this->db->useSqlFile(__DIR__ . '/test-dump-pgsql-insert-table.sql');
            static::assertTrue($success);
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
     */
    public function testSelectAll(): void
    {
        try {
            $sql = 'SELECT * FROM test_select';
            $rows = $this->db->selectAll($sql);
            static::assertSame($this->data, $rows);

            $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
            $params = ['rank' => 20];
            $rows = $this->db->selectAll($sql, $params);
            $data[] = $this->data[2];
            $data[] = $this->data[3];
            $data[] = $this->data[4];
            static::assertSame($data, $rows);

            $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
            $params = ['rank' => 100];
            $rows = $this->db->selectAll($sql, $params);
            static::assertSame([], $rows);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    public function testSelectAllException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Error Execute');

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';

        $this->db->selectAll($sql);
    }

    /**
     * @throws DatabaseException
     */
    public function testSelectRow(): void
    {
        try {
            $sql = 'SELECT * FROM test_select';
            $row = $this->db->selectRow($sql);
            static::assertSame($this->data[0], $row);

            $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
            $params = ['rank' => 20];
            $row = $this->db->selectRow($sql, $params);
            static::assertSame($this->data[2], $row);

            $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
            $params = ['rank' => 100];
            $row = $this->db->selectRow($sql, $params);
            static::assertSame([], $row);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    public function testSelectRowException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Error Execute');

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';

        $this->db->selectRow($sql);
    }

    /**
     * @throws DatabaseException
     */
    public function testSelectCol(): void
    {
        try {
            $sql = 'SELECT * FROM test_select';
            $col = $this->db->selectCol($sql);
            static::assertSame([1, 2, 3, 4, 5, 6], $col);

            $sql = 'SELECT name FROM test_select WHERE rank >= :rank';
            $params = ['rank' => 20];
            $col = $this->db->selectCol($sql, $params);
            static::assertSame(['C', 'D', 'E'], $col);

            $sql = 'SELECT rank FROM test_select WHERE rank >= :rank';
            $params = ['rank' => 100];
            $col = $this->db->selectCol($sql, $params);
            static::assertSame([], $col);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    public function testSelectColException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Error Execute');

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';

        $this->db->selectCol($sql);
    }

    /**
     * @throws DatabaseException
     */
    public function testSelectVar(): void
    {
        try {
            $sql = 'SELECT id FROM test_select';
            $var = $this->db->selectVar($sql);
            static::assertSame(1, $var);

            $sql = 'SELECT name FROM test_select WHERE rank >= :rank';
            $params = ['rank' => 20];
            $var = $this->db->selectVar($sql, $params);
            static::assertSame('C', $var);

            $sql = 'SELECT rank FROM test_select WHERE rank >= :rank';
            $params = ['rank' => 100];
            $var = $this->db->selectVar($sql, $params);
            static::assertFalse($var);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    public function testSelectVarException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Error Execute');

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';

        $this->db->selectVar($sql);
    }

    public function testPdoParamTypeException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Error Bind Value');

        $sql = 'SELECT :array AS array';
        $params = ['array' => []];
        $this->db->selectRow($sql, $params);
    }

    public function testPrepareBindException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('SQLSTATE[HY093]: Invalid parameter number: ::a');

        $sql = 'SELECT :a';
        $params = [':a' => 'a'];
        $this->db->selectRow($sql, $params);
    }

    /**
     * @throws DatabaseException
     * @noinspection GetClassUsageInspection
     */
    public function testSelect(): void
    {
        try {
            $sql = 'SELECT * FROM test_select';
            $statement = $this->db->select($sql);
            static::assertSame(PDOStatement::class, get_class($statement));

            $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
            $params = ['rank' => 20];
            $statement = $this->db->select($sql, $params);
            static::assertSame(PDOStatement::class, get_class($statement));

            $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
            $params = ['rank' => 100];
            $statement = $this->db->select($sql, $params);
            static::assertSame(PDOStatement::class, get_class($statement));
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    public function testSelectException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Error Execute');

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';

        $this->db->select($sql);
    }

    /**
     * @throws DatabaseException
     * @noinspection PhpAssignmentInConditionInspection
     */
    public function testRead(): void
    {
        try {
            $sql = 'SELECT * FROM test_select';
            $statement = $this->db->select($sql);
            $row = $this->db->read($statement);
            static::assertCount(4, $row);

            $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
            $params = ['rank' => 20];
            $statement = $this->db->select($sql, $params);
            $row = $this->db->read($statement);
            static::assertCount(4, $row);

            $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
            $params = ['rank' => 100];
            $statement = $this->db->select($sql, $params);
            $row = $this->db->read($statement);
            static::assertFalse($row);

            $rows = [];
            $statement = $this->db->select('SELECT * FROM test_select');
            while ($row = $this->db->read($statement)) {
                $rows[] = $row;
            }

            static::assertCount(6, $rows);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testReadAll(): void
    {
        try {
            $sql = 'SELECT * FROM test_select';
            $statement = $this->db->select($sql);
            $rows = $this->db->readAll($statement);
            static::assertCount(6, $rows);

            $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
            $params = ['rank' => 20];
            $statement = $this->db->select($sql, $params);
            $rows = $this->db->readAll($statement);
            static::assertCount(3, $rows);

            $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
            $params = ['rank' => 100];
            $statement = $this->db->select($sql, $params);
            $rows = $this->db->readAll($statement);
            static::assertCount(0, $rows);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testTransaction(): void
    {
        try {
            $this->db->startTransaction();

            $sql = 'UPDATE test_select SET name = :name WHERE id =:id';
            $params = ['name' => 'my name', 'id' => 1];
            $this->db->update($sql, $params);

            $this->db->completeTransaction();

            $sql = 'SELECT name FROM test_select WHERE id = :id';
            $params = ['id' => 1];
            static::assertSame('my name', $this->db->selectVar($sql, $params));
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testTransactionError(): void
    {
        try {
            $this->db->startTransaction();

            $sql = 'UPDATE test_select SET name = :name WHERE id =:id';
            $params = ['name' => 'A', 'id' => 1];
            $this->db->update($sql, $params);

            $sql = 'SELECT name FROM test_select WHERE id = :id';
            $params = ['id' => 1];
            static::assertSame('A', $this->db->selectVar($sql, $params));

            $this->db->selectVar($sql);

            $this->db->commitTransaction();
        } catch (DatabaseException $e) {
            $this->db->rollbackTransaction();

            $sql = 'SELECT name FROM test_select WHERE id = :id';
            $params = ['id' => 1];
            static::assertSame('my name', $this->db->selectVar($sql, $params));
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testTransactionException(): void
    {
        try {
            $this->db->startTransaction();

            $sql = 'UPDATE test_select SET name = :name WHERE id =:id';
            $params = ['name' => 'A', 'id' => 1];
            $this->db->update($sql, $params);

            $this->db->selectVar($sql);

            $this->db->completeTransaction();
        } catch (DatabaseException $e) {
            $this->db->completeTransaction();

            $sql = 'SELECT name FROM test_select WHERE id = :id';
            $params = ['id' => 1];
            static::assertSame('my name', $this->db->selectVar($sql, $params));
        }
    }

    // errors

    public function testErrors(): void
    {
        static::assertFalse($this->db->hasErrors());
        static::assertSame([], $this->db->getErrors());
        static::assertNull($this->db->getLastError());

        try {
            $this->db->selectVar('SELECT name FROM test WHERE id = :id');
        } catch (DatabaseException $e) {
            static::assertTrue($this->db->hasErrors());
            static::assertCount(4, $this->db->getLastError());

            $this->db->cleanErrors();

            static::assertFalse($this->db->hasErrors());
            static::assertSame([], $this->db->getErrors());
            static::assertNull($this->db->getLastError());
        }
    }

    // save queries

    /**
     * @throws DatabaseException
     */
    public function testSaveQueries(): void
    {
        try {
            static::assertFalse($this->db->hasSaveQueries());

            $this->db->enableSaveQueries();

            static::assertTrue($this->db->hasSaveQueries());

            $this->db->disableSaveQueries();

            static::assertFalse($this->db->hasSaveQueries());

            $this->db->enableSaveQueries();

            static::assertTrue($this->db->hasSaveQueries());

            static::assertSame([], $this->db->getSavedQueries());

            $this->db->selectAll('SELECT * FROM test_select');

            $queries = $this->db->getSavedQueries();

            static::assertCount(2, $queries);

            $this->db->cleanSavedQueries();

            $queries = $this->db->getSavedQueries();

            static::assertCount(0, $queries);
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    // specific command

    /**
     * @throws DatabaseException
     */
    public function testTruncateTable(): void
    {
        try {
            static::assertTrue($this->db->truncateTable('test'));
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
            static::assertTrue($this->db->truncateTables(['test', 'test_select']));
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testDropTable(): void
    {
        try {
            static::assertTrue($this->db->dropTable('test'));
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function testDropTables(): void
    {
        try {
            static::assertTrue($this->db->dropTables(['test', 'toto']));
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    // low level

    /**
     * @throws DatabaseException
     * @noinspection GetClassUsageInspection
     */
    public function testConnect(): void
    {
        try {
            static::assertNull($this->db->getPdo());

            $this->db->connect();

            static::assertSame('PDO', get_class($this->db->getPdo()));
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     * @noinspection GetClassUsageInspection
     */
    public function testGetPdo(): void
    {
        try {
            static::assertNull($this->db->getPdo());

            $this->db->connect();

            static::assertSame('PDO', get_class($this->db->getPdo()));
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }

    /**
     * @throws DatabaseException
     * @noinspection GetClassUsageInspection
     */
    public function testDisconnect(): void
    {
        try {
            $this->db->connect();

            static::assertSame('PDO', get_class($this->db->getPdo()));

            $this->db->disconnect();

            static::assertNull($this->db->getPdo());
        } catch (DatabaseException $e) {
            var_dump($this->db->getErrors());
            throw $e;
        }
    }
}
