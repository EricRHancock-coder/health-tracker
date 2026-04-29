<?php

namespace Tests\Utils;

use PHPUnit\Framework\TestCase;
use App\Utils\Database;

class DatabaseTest extends TestCase {
    private Database $db;

    protected function setUp(): void {
        $this->db = Database::getInstance();
        $this->db->setTestDsn('sqlite::memory:');
        
        // Create a dummy table for testing
        $this->db->execute("CREATE TABLE test_table (id INTEGER PRIMARY KEY, name TEXT)");
    }

    public function testExecuteAndQuery(): void {
        // Test Insert
        $count = $this->db->execute("INSERT INTO test_table (name) VALUES (?)", ['Alice']);
        $this->assertEquals(1, $count);

        // Test Last Insert ID
        $id = $this->db->lastInsertId();
        $this->assertNotEmpty($id);

        // Test Query
        $results = $this->db->query("SELECT * FROM test_table WHERE name = ?", ['Alice']);
        $this->assertCount(1, $results);
        $this->assertEquals('Alice', $results[0]['name']);
    }

    public function testUpdateAndDelete(): void {
        $this->db->execute("INSERT INTO test_table (name) VALUES (?)", ['Bob']);
        
        // Test Update
        $this->db->execute("UPDATE test_table SET name = ? WHERE name = ?", ['Robert', 'Bob']);
        $results = $this->db->query("SELECT name FROM test_table WHERE name = ?", ['Robert']);
        $this->assertCount(1, $results);

        // Test Delete
        $this->db->execute("DELETE FROM test_table WHERE name = ?", ['Robert']);
        $results = $this->db->query("SELECT * FROM test_table");
        $this->assertCount(0, $results);
    }
}
