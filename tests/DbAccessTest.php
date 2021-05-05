<?php

declare(strict_types=1);

namespace Boronczyk\Alistair\Test;

error_reporting(E_ALL);
ini_set('display_errors', '1');

use PHPUnit\Framework\TestCase;
use Boronczyk\Alistair\DbAccess;

final class DbAccessTest extends TestCase
{
    private static $pdo;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = new \PDO('sqlite::memory:');
        self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        self::$pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        self::$pdo->query("CREATE TABLE foo (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            bar TEXT NOT NULL,
            baz TEXT NOT NULL
        )");
        self::$pdo->query("INSERT INTO foo VALUES (1, 'qwerty', 'asdfg')");
        self::$pdo->query("INSERT INTO foo VALUES (2, 'dvorak', 'aoeui')");
    }

    public static function tearDownAfterClass(): void
    {
        self::$pdo = null;
    }

    public function testQuery(): void
    {
        $db = new DbAccess(self::$pdo);
        $db->query("INSERT INTO foo (id, bar, baz) VALUES (3, 'hello', 'world')");

        $result = self::$pdo->query('SELECT bar, baz FROM foo WHERE id = 3');
        $this->assertEquals(['bar' => 'hello', 'baz' => 'world'], $result->fetch());
    }

    public function testQueryWithParams(): void
    {
        $db = new DbAccess(self::$pdo);
        $db->query('INSERT INTO foo (id, bar, baz) VALUES (?, ?, ?)', [4, 'hola', 'mundo']);

        $result = self::$pdo->query('SELECT bar, baz FROM foo WHERE id = 4');
        $this->assertEquals(['bar' => 'hola', 'baz' => 'mundo'], $result->fetch());
    }

    public function testQueryException(): void
    {
        $db = new DbAccess(self::$pdo);

        $this->expectException(\PDOException::class);
        $db->query('INSERT INTO foo (fizz) VALUES (NULL)');
    }

    public function testQueryRow(): void
    {
        $db = new DbAccess(self::$pdo);
        $row = $db->queryRow('SELECT bar, baz FROM foo WHERE id = 1');

        $this->assertEquals(['bar' => 'qwerty', 'baz' => 'asdfg'], $row);
    }

    public function testQueryRowWithParams(): void
    {
        $db = new DbAccess(self::$pdo);
        $row = $db->queryRow('SELECT bar, baz FROM foo WHERE id = ?', [1]);

        $this->assertEquals(['bar' => 'qwerty', 'baz' => 'asdfg'], $row);
    }

    public function testQueryRowWithClassname(): void
    {
        $db = new DbAccess(self::$pdo);
        $row = $db->queryRow(
            'SELECT id, bar, baz FROM foo WHERE id = 1',
            null,
            FooModel::class
        );

        $this->assertEquals(1, $row->id);
        $this->assertEquals('qwerty', $row->bar);
        $this->assertEquals('asdfg', $row->baz);
    }

    public function testQueryRowNoResults(): void
    {
        $db = new DbAccess(self::$pdo);
        $row = $db->queryRow('SELECT bar, baz FROM foo WHERE id = 42');

        $this->assertIsArray($row);
        $this->assertEmpty($row);
    }

    public function testQueryRowException(): void
    {
        $db = new DbAccess(self::$pdo);

        $this->expectException(\PDOException::class);
        $db->queryRow('SELECT bar FROM foo WHERE fizz = 42');
    }

    public function testQueryRows(): void
    {
        $db = new DbAccess(self::$pdo);
        $rows = $db->queryRows('SELECT id, bar, baz FROM foo WHERE id IN (1, 2) ORDER BY id');

        $this->assertEquals(2, count($rows));
        $this->assertEquals(['id' => 1, 'bar' => 'qwerty', 'baz' => 'asdfg'], $rows[0]);
        $this->assertEquals(['id' => 2, 'bar' => 'dvorak', 'baz' => 'aoeui'], $rows[1]);
    }

    public function testQueryRowsWithParams(): void
    {
        $db = new DbAccess(self::$pdo);
        $rows = $db->queryRows('SELECT id, bar, baz FROM foo WHERE id = ? OR id = ? ORDER BY id', [1, 2]);

        $this->assertEquals(2, count($rows));
        $this->assertEquals(['id' => 1, 'bar' => 'qwerty', 'baz' => 'asdfg'], $rows[0]);
        $this->assertEquals(['id' => 2, 'bar' => 'dvorak', 'baz' => 'aoeui'], $rows[1]);
    }

    public function testQueryRowsWithClassname(): void
    {
        $db = new DbAccess(self::$pdo);
        $rows = $db->queryRows(
            'SELECT id, bar, baz FROM foo WHERE id IN (1, 2) ORDER BY id',
            null,
            FooModel::class
        );

        $this->assertEquals(2, count($rows));
        $this->assertEquals(1, $rows[0]->id);
        $this->assertEquals('qwerty', $rows[0]->bar);
        $this->assertEquals('asdfg', $rows[0]->baz);
        $this->assertEquals(2, $rows[1]->id);
        $this->assertEquals('dvorak', $rows[1]->bar);
        $this->assertEquals('aoeui', $rows[1]->baz);
    }

    public function testQueryRowsNoResults(): void
    {
        $db = new DbAccess(self::$pdo);
        $rows = $db->queryRows('SELECT id, bar, baz FROM foo WHERE id > 42');

        $this->assertIsArray($rows);
        $this->assertEmpty($rows);
    }

    public function testQueryRowsException(): void
    {
        $db = new DbAccess(self::$pdo);

        $this->expectException(\PDOException::class);
        $db->queryRows('SELECT bar FROM foo WHERE fizz = 42');
    }

    public function testQueryValue(): void
    {
        $db = new DbAccess(self::$pdo);
        $value = $db->queryValue('SELECT bar FROM foo WHERE id = 1');

        $this->assertEquals('qwerty', $value);
    }

    public function testQueryValueWithParams(): void
    {
        $db = new DbAccess(self::$pdo);
        $value = $db->queryValue('SELECT bar FROM foo WHERE id = ?', [1]);

        $this->assertEquals('qwerty', $value);
    }

    public function testQueryValueFalsyValue(): void
    {
        $db = new DbAccess(self::$pdo);
        $value = $db->queryValue('SELECT 0');

        $this->assertNotNull($value);
    }

    public function testQueryValueNoResult(): void
    {
        $db = new DbAccess(self::$pdo);
        $value = $db->queryValue('SELECT bar FROM foo WHERE id = 42');

        $this->assertNull($value);
    }

    public function testQueryValueException(): void
    {
        $db = new DbAccess(self::$pdo);

        $this->expectException(\PDOException::class);
        $db->queryValue('SELECT bar FROM foo WHERE fizz = 42');
    }
}
