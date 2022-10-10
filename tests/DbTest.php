<?php
use PHPUnit\Framework\TestCase;
use piko\Db;

class DbTest extends TestCase
{
    public function testDb()
    {
        $db = new Db(['dsn' => 'sqlite::memory:']);
        $this->assertInstanceOf(PDO::class, $db);
    }
}
