<?php
use Piko\Tests\AbstractTestDbRecord;

class MsSqlDbRecordTest extends AbstractTestDbRecord
{
    public static function setUpBeforeClass(): void
    {
        $db = new PDO(
            'dblib:host=' . $_ENV['MSSQL_HOST'],
            $_ENV['MSSQL_USER'],
            $_ENV['MSSQL_PASSWORD']
        );

        $db->exec('IF DB_ID(\'test\') IS NULL CREATE DATABASE test');

        self::$db = new PDO(
            'dblib:host=' . $_ENV['MSSQL_HOST'] . ';dbname=test',
            $_ENV['MSSQL_USER'],
            $_ENV['MSSQL_PASSWORD']
        );

        $query = <<<EOL
IF OBJECT_ID('contact', 'U') IS NULL
CREATE TABLE contact (
  id INT IDENTITY(1,1) PRIMARY KEY,
  name NVARCHAR(255) NULL,
  firstname NVARCHAR(255),
  lastname NVARCHAR(255),
  age INT NULL,
  [order] INT,
  active BIT DEFAULT 0 NULL,
  active2 BIT DEFAULT 1,
  income FLOAT DEFAULT 0
);
EOL;
        self::$db->exec($query);
    }

    public static function tearDownAfterClass(): void
    {
        self::$db->exec('DROP TABLE contact');

        self::$db = null;
    }

    protected function setUp(): void
    {
        self::$db->exec('TRUNCATE TABLE contact');
    }
}
