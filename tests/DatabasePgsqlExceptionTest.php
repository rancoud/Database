<?php

namespace Rancoud\Database\Test;

use Exception;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Rancoud\Database\Configurator;
use Rancoud\Database\Database;

/**
 * Class DatabaseMysqlExceptionTest.
 */
class DatabasePgsqlExceptionTest extends TestCase
{
    /** @var Database */
    protected $db;

    protected $params = [
        'engine'       => 'pgsql',
        'host'         => '127.0.0.1',
        'user'         => 'postgres',
        'password'     => '',
        'database'     => 'test_database',
        'report_error' => 'exception'
    ];

    protected $data = [
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

    public function setUp()
    {
        $databaseConf = new Configurator($this->params);
        $this->db = new Database($databaseConf);
    }

    public function tearDown()
    {
        $this->db->disconnect();
        $this->db = null;
    }

    public function testFirstLaunch()
    {
        $success = $this->db->dropTables(['test', 'test_select']);

        static::assertTrue($success);
    }

    public function testExec()
    {
        $success = $this->db->exec('CREATE TABLE test (
    id SERIAL PRIMARY KEY,
    name character varying(255) NOT NULL
);');

        static::assertTrue($success);
    }

    public function testExecError()
    {
        try {
            $this->db->exec('aaa');
        } catch (Exception $e) {
            static::assertSame('Error Execute', $e->getMessage());
        }
    }

    public function testExecException()
    {
        static::expectException(Exception::class);

        $this->db->exec('aaa');
    }

    public function testInsert()
    {
        $sql = "INSERT INTO test (name) VALUES ('A')";
        $id = $this->db->insert($sql);
        static::assertSame(true, $id);

        $count = $this->db->count("SELECT COUNT(*) FROM test WHERE name='A' AND id=1");
        static::assertSame(1, $count);

        $sql = 'INSERT INTO test (name) VALUES (:name)';
        $params = ['name' => 'B'];
        $id = $this->db->insert($sql, $params);
        static::assertSame(true, $id);

        $count = $this->db->count("SELECT COUNT(*) FROM test WHERE name='B' AND id=2");
        static::assertSame(1, $count);

        $params = ['name' => 'C'];
        $getLastInsertId = true;
        $id = $this->db->insert($sql, $params, $getLastInsertId);
        static::assertSame(3, $id);

        $count = $this->db->count("SELECT COUNT(*) FROM test WHERE name='C' AND id=3");
        static::assertSame(1, $count);
    }

    public function testInsertError()
    {
        $sql = 'INSERT INTO test (name) VALUES (:name)';

        try {
            $this->db->insert($sql);
        } catch (Exception $e) {
            static::assertSame('Error Execute', $e->getMessage());
        }
    }

    public function testInsertException()
    {
        static::expectException(Exception::class);

        $sql = 'INSERT INTO test (name) VALUES (:name)';

        $this->db->insert($sql);
    }

    public function testUpdate()
    {
        $sql = "UPDATE test SET name = 'AA' WHERE id = 1";
        $rowsAffected = $this->db->update($sql);
        static::assertSame(true, $rowsAffected);

        $count = $this->db->count("SELECT COUNT(*) FROM test WHERE name='AA' AND id=1");
        static::assertSame(1, $count);

        $sql = 'UPDATE test SET name = :name WHERE id = :id';
        $params = ['id' => 2, 'name' => 'BB'];
        $rowsAffected = $this->db->update($sql, $params);
        static::assertSame(true, $rowsAffected);

        $count = $this->db->count("SELECT COUNT(*) FROM test WHERE name='BB' AND id=2");
        static::assertSame(1, $count);

        $params = ['id' => 3, 'name' => 'CC'];
        $getCountRowsAffected = true;
        $rowsAffected = $this->db->update($sql, $params, $getCountRowsAffected);
        static::assertSame(1, $rowsAffected);

        $count = $this->db->count("SELECT COUNT(*) FROM test WHERE name='CC' AND id=3");
        static::assertSame(1, $count);
    }

    public function testUpdateError()
    {
        $sql = 'UPDATE test SET name = :name WHERE id = :id';

        try {
            $this->db->update($sql);
        } catch (Exception $e) {
            static::assertSame('Error Execute', $e->getMessage());
        }
    }

    public function testUpdateException()
    {
        static::expectException(Exception::class);

        $sql = 'UPDATE test SET name = :name WHERE id = :id';

        $this->db->update($sql);
    }

    public function testDelete()
    {
        $sql = 'DELETE FROM test WHERE id = 1';
        $rowsAffected = $this->db->delete($sql);
        static::assertSame(true, $rowsAffected);

        $count = $this->db->count('SELECT COUNT(*) FROM test WHERE id=1');
        static::assertSame(0, $count);

        $sql = 'DELETE FROM test WHERE id = :id';
        $params = ['id' => 2];
        $rowsAffected = $this->db->delete($sql, $params);
        static::assertSame(true, $rowsAffected);

        $count = $this->db->count('SELECT COUNT(*) FROM test WHERE id=2');
        static::assertSame(0, $count);

        $params = ['id' => 3];
        $getCountRowsAffected = true;
        $rowsAffected = $this->db->delete($sql, $params, $getCountRowsAffected);
        static::assertSame(1, $rowsAffected);

        $count = $this->db->count('SELECT COUNT(*) FROM test WHERE id=3');
        static::assertSame(0, $count);
    }

    public function testDeleteError()
    {
        $sql = 'DELETE FROM test WHERE id = :id';

        try {
            $this->db->delete($sql);
        } catch (Exception $e) {
            static::assertSame('Error Execute', $e->getMessage());
        }
    }

    public function testDeleteException()
    {
        static::expectException(Exception::class);

        $sql = 'DELETE FROM test WHERE id = :id';

        $this->db->delete($sql);
    }

    public function testUseSqlFile()
    {
        $success = $this->db->useSqlFile(__DIR__ . '/test-dump-pgsql-create-table.sql');
        static::assertTrue($success);

        $success = $this->db->useSqlFile(__DIR__ . '/test-dump-pgsql-insert-table.sql');
        static::assertTrue($success);
    }

    public function testUseSqlFileException()
    {
        static::expectException(Exception::class);

        $this->db->useSqlFile('./missing-dump.sql');
    }

    public function testSelectAll()
    {
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
    }

    public function testSelectAllError()
    {
        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';

        try {
            $this->db->selectAll($sql);
        } catch (Exception $e) {
            static::assertSame('Error Execute', $e->getMessage());
        }
    }

    public function testSelectAllException()
    {
        static::expectException(Exception::class);

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';

        $this->db->selectAll($sql);
    }

    public function testSelectRow()
    {
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
    }

    public function testSelectRowError()
    {
        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';

        try {
            $this->db->selectRow($sql);
        } catch (Exception $e) {
            static::assertSame('Error Execute', $e->getMessage());
        }
    }

    public function testSelectRowException()
    {
        static::expectException(Exception::class);

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';

        $this->db->selectRow($sql);
    }

    public function testSelectCol()
    {
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
    }

    public function testSelectColError()
    {
        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';

        try {
            $this->db->selectCol($sql);
        } catch (Exception $e) {
            static::assertSame('Error Execute', $e->getMessage());
        }
    }

    public function testSelectColException()
    {
        static::expectException(Exception::class);

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';

        $this->db->selectCol($sql);
    }

    public function testSelectVar()
    {
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
    }

    public function testSelectVarError()
    {
        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';

        try {
            $this->db->selectVar($sql);
        } catch (Exception $e) {
            static::assertSame('Error Execute', $e->getMessage());
        }
    }

    public function testSelectVarException()
    {
        static::expectException(Exception::class);

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';

        $this->db->selectVar($sql);
    }

    public function testPdoParamTypeError()
    {
        $sql = 'SELECT :array AS array';
        $params = ['array' => []];

        try {
            $this->db->selectRow($sql, $params);
        } catch (Exception $e) {
            static::assertSame('Error Bind Value', $e->getMessage());
        }
    }

    public function testPdoParamTypeException()
    {
        static::expectException(Exception::class);

        $sql = 'SELECT :array AS array';
        $params = ['array' => []];
        $this->db->selectRow($sql, $params);
    }

    public function testPrepareBindException()
    {
        static::expectException(Exception::class);

        $sql = 'SELECT :a';
        $params = [':a' => 'a'];
        $this->db->selectRow($sql, $params);
    }

    public function testSelect()
    {
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
    }

    public function testSelectError()
    {
        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';

        try {
            $this->db->select($sql);
        } catch (Exception $e) {
            static::assertSame('Error Execute', $e->getMessage());
        }
    }

    public function testSelectException()
    {
        static::expectException(Exception::class);

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';

        $this->db->select($sql);
    }

    public function testRead()
    {
        $sql = 'SELECT * FROM test_select';
        $statement = $this->db->select($sql);
        $row = $this->db->read($statement);
        static::assertSame(4, count($row));

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
        $params = ['rank' => 20];
        $statement = $this->db->select($sql, $params);
        $row = $this->db->read($statement);
        static::assertSame(4, count($row));

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

        static::assertSame(6, count($rows));
    }

    public function testReadAll()
    {
        $sql = 'SELECT * FROM test_select';
        $statement = $this->db->select($sql);
        $rows = $this->db->readAll($statement);
        static::assertSame(6, count($rows));

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
        $params = ['rank' => 20];
        $statement = $this->db->select($sql, $params);
        $rows = $this->db->readAll($statement);
        static::assertSame(3, count($rows));

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
        $params = ['rank' => 100];
        $statement = $this->db->select($sql, $params);
        $rows = $this->db->readAll($statement);
        static::assertSame(0, count($rows));
    }

    public function testTransaction()
    {
        $this->db->startTransaction();

        $sql = 'UPDATE test_select SET name = :name WHERE id =:id';
        $params = ['name' => 'my name', 'id' => 1];
        $this->db->update($sql, $params);

        $this->db->completeTransaction();

        $sql = 'SELECT name FROM test_select WHERE id = :id';
        $params = ['id' => 1];
        static::assertSame('my name', $this->db->selectVar($sql, $params));
    }

    public function testTransactionError()
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
        } catch (Exception $e) {
            $this->db->rollbackTransaction();

            $sql = 'SELECT name FROM test_select WHERE id = :id';
            $params = ['id' => 1];
            static::assertSame('my name', $this->db->selectVar($sql, $params));
        }
    }

    public function testTransactionException()
    {
        try {
            $this->db->startTransaction();

            $sql = 'UPDATE test_select SET name = :name WHERE id =:id';
            $params = ['name' => 'A', 'id' => 1];
            $this->db->update($sql, $params);

            $this->db->selectVar($sql);

            $this->db->completeTransaction();
        } catch (Exception $e) {
            $this->db->completeTransaction();

            $sql = 'SELECT name FROM test_select WHERE id = :id';
            $params = ['id' => 1];
            static::assertSame('my name', $this->db->selectVar($sql, $params));
        }
    }

    // errors

    public function testErrors()
    {
        static::assertFalse($this->db->hasErrors());
        static::assertSame([], $this->db->getErrors());
        static::assertSame(null, $this->db->getLastError());

        try {
            $this->db->selectVar('SELECT name FROM test WHERE id = :id');
        } catch (Exception $e) {
            static::assertTrue($this->db->hasErrors());
            static::assertSame(4, count($this->db->getLastError()));

            $this->db->cleanErrors();

            static::assertFalse($this->db->hasErrors());
            static::assertSame([], $this->db->getErrors());
            static::assertSame(null, $this->db->getLastError());
        }
    }

    // save queries

    public function testSaveQueries()
    {
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

        static::assertSame(2, count($queries));

        $this->db->cleanSavedQueries();

        $queries = $this->db->getSavedQueries();

        static::assertSame(0, count($queries));
    }

    // specific command

    public function testTruncateTable()
    {
        static::assertTrue($this->db->truncateTable('test'));
    }

    public function testTruncateTables()
    {
        static::assertTrue($this->db->truncateTables(['test', 'test_select']));
    }

    public function testDropTable()
    {
        static::assertTrue($this->db->dropTable('test'));
    }

    public function testDropTables()
    {
        static::assertTrue($this->db->dropTables(['test', 'toto']));
    }

    // low level

    public function testConnect()
    {
        static::assertNull($this->db->getPdo());

        $this->db->connect();

        static::assertSame('PDO', get_class($this->db->getPdo()));
    }

    public function testGetPdo()
    {
        static::assertNull($this->db->getPdo());

        $this->db->connect();

        static::assertSame('PDO', get_class($this->db->getPdo()));
    }

    public function testDisconnect()
    {
        $this->db->connect();

        static::assertSame('PDO', get_class($this->db->getPdo()));

        $this->db->disconnect();

        static::assertNull($this->db->getPdo());
    }
}
