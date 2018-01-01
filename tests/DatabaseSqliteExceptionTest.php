<?php

namespace Rancoud\Database\Test;

use Exception;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Rancoud\Database\Configurator;
use Rancoud\Database\Database;
use TypeError;

/**
 * Class DatabaseSqliteExceptionTest.
 */
class DatabaseSqliteExceptionTest extends TestCase
{
    /** @var Database */
    protected $db;

    protected $params = [
        'engine'       => 'sqlite',
        'host'         => '127.0.0.1',
        'user'         => '',
        'password'     => '',
        'database'     => __DIR__ . '/test_database.db',
        'report_error' => 'exception'
    ];

    public function setUp()
    {
        $databaseConf = new Configurator($this->params);
        $this->db = new Database($databaseConf);
    }

    public function tearDown()
    {
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
            id   INTEGER       PRIMARY KEY AUTOINCREMENT,
            name VARCHAR (255) NOT NULL
        );');

        static::assertTrue($success);
    }

    public function testExecError()
    {
        try {
            $this->db->exec('aaa');
        } catch (Exception $e) {
            static::assertSame('Error Prepare Statement', $e->getMessage());
        }
    }

    public function testExecException()
    {
        static::expectException(Exception::class);

        $this->db->exec('aaa');
    }

    public function testInsert()
    {
        $sql = 'INSERT INTO test (name) VALUES ("A")';
        $id = $this->db->insert($sql);
        static::assertTrue($id);

        $sql = 'INSERT INTO test (name) VALUES (:name)';
        $params = ['name' => 'B'];
        $id = $this->db->insert($sql, $params);
        static::assertTrue($id);

        $params = ['name' => 'C'];
        $getLastInsertId = true;
        $id = $this->db->insert($sql, $params, $getLastInsertId);
        static::assertSame(3, $id);
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
        $sql = 'UPDATE test SET name = "AA" WHERE id = 1';
        $rowsAffected = $this->db->update($sql);
        static::assertTrue($rowsAffected);

        $sql = 'UPDATE test SET name = :name WHERE id = :id';
        $params = ['id' => 2, 'name' => 'BB'];
        $rowsAffected = $this->db->update($sql, $params);
        static::assertTrue($rowsAffected);

        $params = ['id' => 3, 'name' => 'CC'];
        $getCountRowsAffected = true;
        $rowsAffected = $this->db->update($sql, $params, $getCountRowsAffected);
        static::assertSame(1, $rowsAffected);
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

    /*public function testUpdateException()
    {
        static::expectException(Exception::class);

        $sql = 'UPDATE test SET name = :name WHERE id = :id';

        $this->db->update($sql);
    }*/

    public function testDelete()
    {
        $sql = 'DELETE FROM test WHERE id = 1';
        $rowsAffected = $this->db->delete($sql);
        static::assertTrue($rowsAffected);

        $sql = 'DELETE FROM test WHERE id = :id';
        $params = ['id' => 2];
        $rowsAffected = $this->db->delete($sql, $params);
        static::assertTrue($rowsAffected);

        $params = ['id' => 3];
        $getCountRowsAffected = true;
        $rowsAffected = $this->db->delete($sql, $params, $getCountRowsAffected);
        static::assertSame(1, $rowsAffected);
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

    /*public function testDeleteException()
    {
        static::expectException(Exception::class);

        $sql = 'DELETE FROM test WHERE id = :id';

        $this->db->delete($sql);
    }*/

    public function testUseSqlFile()
    {
        $this->db->exec('CREATE TABLE test_select (
            id      INT          PRIMARY KEY
            NOT NULL,
            name    VARCHAR (45) NOT NULL,
            rank    INT (1)      NOT NULL,
            comment TEXT
            );');
        $this->db->exec("INSERT INTO test_select (id, name, rank, comment) VALUES (1, 'A', 0, NULL);");
        $this->db->exec("INSERT INTO test_select (id, name, rank, comment) VALUES (2, 'B', 10, 'yes');");
        $this->db->exec("INSERT INTO test_select (id, name, rank, comment) VALUES (3, 'C', 20, 'maybe');");
        $this->db->exec("INSERT INTO test_select (id, name, rank, comment) VALUES (4, 'D', 30, 'no');");
        $this->db->exec("INSERT INTO test_select (id, name, rank, comment) VALUES (5, 'E', 25, NULL);");
        $this->db->exec("INSERT INTO test_select (id, name, rank, comment) VALUES (6, 'F', 5, NULL);");
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
        //var_dump($rows);
        static::assertSame(6, count($rows));

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
        $params = ['rank' => 20];
        $rows = $this->db->selectAll($sql, $params);
        //var_dump($rows);
        static::assertSame(3, count($rows));

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
        $params = ['rank' => 100];
        $rows = $this->db->selectAll($sql, $params);
        //var_dump($rows);
        static::assertSame(0, count($rows));
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

    /*public function testSelectAllException()
    {
        static::expectException(Exception::class);

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';

        $this->db->selectAll($sql);
    }*/

    public function testSelectRow()
    {
        $sql = 'SELECT * FROM test_select';
        $rows = $this->db->selectRow($sql);
        //var_dump($rows);
        static::assertSame(4, count($rows));

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
        $params = ['rank' => 20];
        $rows = $this->db->selectRow($sql, $params);
        //var_dump($rows);
        static::assertSame(4, count($rows));

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
        $params = ['rank' => 100];
        $rows = $this->db->selectRow($sql, $params);
        //var_dump($rows);
        static::assertSame([], $rows);
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

    /*public function testSelectRowException()
    {
        static::expectException(Exception::class);

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';

        $this->db->selectRow($sql);
    }*/

    public function testSelectCol()
    {
        $sql = 'SELECT * FROM test_select';
        $rows = $this->db->selectCol($sql);
        static::assertSame(6, count($rows));

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
        $params = ['rank' => 20];
        $rows = $this->db->selectCol($sql, $params);
        static::assertSame(3, count($rows));

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
        $params = ['rank' => 100];
        $rows = $this->db->selectCol($sql, $params);
        static::assertSame(0, count($rows));
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

    /*public function testSelectColException()
    {
        static::expectException(Exception::class);

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';

        $this->db->selectCol($sql);
    }*/

    public function testSelectVar()
    {
        $sql = 'SELECT id FROM test_select';
        $var = $this->db->selectVar($sql);
        static::assertSame('1', $var);

        $sql = 'SELECT id FROM test_select WHERE rank >= :rank';
        $params = ['rank' => 20];
        $var = $this->db->selectVar($sql, $params);
        static::assertSame('3', $var);

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';
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

    /*public function testSelectVarException()
    {
        static::expectException(Exception::class);

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';

        $this->db->selectVar($sql);
    }*/

    public function testPdoParamType()
    {
        $sql = 'SELECT :true AS `true`, :false AS `false`, :null AS `null`, :float AS `float`,
                :int AS `int`, :string AS `string`, :resource AS `resource`';
        $params = [
            'true'    => true,
            'false'   => false,
            'null'    => null,
            'float'   => 1.2,
            'int'     => 800,
            'string'  => 'string',
            'resource'=> fopen(__DIR__ . '/test-dump-mysql.sql', 'r')
        ];
        $row = $this->db->selectRow($sql, $params);

        static::assertSame('1', $row['true']);
        static::assertSame('0', $row['false']);
        static::assertSame(null, $row['null']);
        static::assertSame('1.2', $row['float']);
        static::assertSame('800', $row['int']);
        static::assertSame('string', $row['string']);
        static::assertSame('-- MySQL dump', mb_substr($row['resource'], 0, 13));
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
        $row = $this->db->selectRow($sql, $params);
        //var_dump($row);
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

    /*public function testSelectException()
    {
        static::expectException(Exception::class);

        $sql = 'SELECT * FROM test_select WHERE rank >= :rank';

        $this->db->select($sql);
    }*/

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

    /*public function testReadError()
    {
        try{
            $this->db->read(null);
        }catch(Exception $e)
        {
            static::assertSame('Error Execute', $e->getMessage());
        }
    }*/

    public function testReadException()
    {
        static::expectException(TypeError::class);

        $this->db->read(null);
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

    /*public function testReadAllError()
    {
        try{
            $this->db->readAll(null);
        }catch(Exception $e)
        {
            //var_dump($e->getMessage());
            static::assertSame('Error Execute', $e->getMessage());
        }
    }*/

    public function testReadAllException()
    {
        static::expectException(TypeError::class);

        $this->db->readAll(null);
    }

    public function testTransaction()
    {
        $this->db->completeTransaction();

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
            $params = ['name' => 'my name', 'id' => 1];
            $this->db->update($sql, $params);

            $sql = 'SELECT name FROM test_select WHERE id = :id';
            $params = ['id' => 1];
            static::assertSame('my name', $this->db->selectVar($sql, $params));

            $this->db->selectVar($sql);

            $this->db->completeTransaction();
        } catch (Exception $e) {
            $this->db->completeTransaction();

            $sql = 'SELECT name FROM test_select WHERE id = :id';
            $params = ['id' => 1];
            //static::assertSame('A', $this->db->selectVar($sql, $params));
        }
    }

    /*public function testTransactionException()
    {
        try{
            $this->db->startTransaction();

            $sql = 'UPDATE test_select SET name = :name WHERE id =:id';
            $params = ['name' => 'my name', 'id' => 1];
            $this->db->update($sql, $params);

            $this->db->completeTransaction();
        }
        catch (Exception $e){
            $sql = 'SELECT name FROM test_select WHERE id = :id';
            $params = ['id' => 1];
            static::assertSame('A', $this->db->selectVar($sql, $params));
        }
    }*/

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

        $this->testSelectAll('SELECT * FROM test_select');

        $queries = $this->db->getSavedQueries();

        static::assertSame(4, count($queries));

        $this->db->cleanSavedQueries();

        $queries = $this->db->getSavedQueries();

        static::assertSame(0, count($queries));
    }

    // specific command

    /*public function testTruncateTable()
    {
        static::assertTrue($this->db->truncateTable('test'));
    }

    public function testTruncateTables()
    {
        static::assertTrue($this->db->truncateTables(['test', 'test_select']));
    }*/

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

    public function testConnectError()
    {
        try {
            $params = $this->params;
            $params['user'] = '';
            $databaseConf = new Configurator($params);
            $db = new Database($databaseConf);
            $db->connect();
        } catch (Exception $e) {
            static::assertSame('Error Connecting Database', $e->getMessage());
        }
    }

    /*public function testConnectException()
    {
        static::expectException(Exception::class);

        $params = $this->params;
        $params['user'] = '';
        $databaseConf = new Configurator($params);
        $db = new Database($databaseConf);
        $db->connect();
    }*/

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

    /*
        public function testDropOneTable()
        {
            static::assertNull(null, $this->db->dropTable('test'));
        }

        public function testDropMultiTable()
        {
            static::assertNull(null, $this->db->dropTables(['test', 'toto']));
        }

        public function testExec()
        {
            $this->db->exec('CREATE TABLE test (
                                        id   INTEGER       PRIMARY KEY AUTOINCREMENT,
                                        name VARCHAR (255) NOT NULL
                                    );');
            static::assertFalse($this->db->hasErrors());
        }

        public function testInsert()
        {
            $id = $this->db->insert('INSERT INTO test (name) VALUES (:name)', ['name' => 'A'], true);
            static::assertSame(1, $id);
        }

        public function testUpdate()
        {
            $sql = 'UPDATE test SET name = :name WHERE id = :id';
            $params = ['id' => 1, 'name' => 'google'];
            $rowsAffected = $this->db->update($sql, $params, true);
            static::assertSame(1, $rowsAffected);
        }

        public function testDelete()
        {
            $rowsAffected = $this->db->delete('DELETE FROM test WHERE name = :name1', ['name1' => 'google'], true);
            static::assertSame(1, $rowsAffected);
        }

        public function testRead()
        {
            $res = [];

            $id = $this->db->insert('INSERT INTO test (name) VALUES (:name)', ['name' => 'A'], true);
            $id = $this->db->insert('INSERT INTO test (name) VALUES (:name)', ['name' => 'B'], true);
            $id = $this->db->insert('INSERT INTO test (name) VALUES (:name)', ['name' => 'C'], true);

            $cursor = $this->db->select('SELECT * FROM test');
            while ($row = $this->db->read($cursor)) {
                $res[] = $row;
            }

            static::assertSame(3, count($res));
        }

        public function testCount()
        {
            $count = $this->db->count('SELECT COUNT(*) FROM test');
            static::assertSame(3, $count);
        }

        public function testSelectAll()
        {
            $rows = $this->db->selectAll('SELECT * FROM test');
            static::assertSame(3, count($rows));
        }

        public function testSelectRow()
        {
            $row = $this->db->selectRow('SELECT * FROM test WHERE id = :id', ['id' => 3]);
            static::assertSame('B', $row['name']);
        }

        public function testSelectCol()
        {
            $col = $this->db->selectCol('SELECT id FROM test');
            static::assertSame(3, count($col));
        }

        public function testSelectVar()
        {
            $var = $this->db->selectVar('SELECT name FROM test WHERE id = :id', ['id' => 3]);
            static::assertSame('B', $var);
        }

        public function testGetError()
        {
            static::expectException(Exception::class);

            $this->db->selectVar('SELECT namebbb FROM test WHERE id = :id', ['id' => 3]);
            static::assertTrue($this->db->hasErrors());
        }

        public function testTruncateTable()
        {
            //$this->db->truncateTable('test');
            //static::assertFalse($this->db->hasErrors());
        }

        public function testTruncateTables()
        {
            //$this->db->truncateTables(['test', 'test']);
            //static::assertFalse($this->db->hasErrors());
        }

        public function testDisconnect()
        {
            $this->db->disconnect();

            static::assertNull($this->db->getPdo());
        }
    */
}
