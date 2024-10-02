<?php
use Piko\Tests\AbstractTestDbRecord;

class MySqlDbRecordTest extends AbstractTestDbRecord
{
    public static function setUpBeforeClass(): void
    {
        $db = new PDO(
            'mysql:host=' . $_ENV['MYSQL_HOST'],
            $_ENV['MYSQL_USER'],
            $_ENV['MYSQL_PASSWORD']
        );

        $db->exec('CREATE DATABASE IF NOT EXISTS test');

        self::$db = new PDO(
            'mysql:host=' . $_ENV['MYSQL_HOST'] . ';dbname=test',
            $_ENV['MYSQL_USER'],
            $_ENV['MYSQL_PASSWORD']
        );

        $query = <<<EOL
CREATE TABLE IF NOT EXISTS contact (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  firstname VARCHAR(255),
  lastname VARCHAR(255),
  `order` INT,
  active INT DEFAULT 0,
  active2 TINYINT DEFAULT 1,
  income FLOAT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
        self::$db->exec('TRUNCATE contact');
    }
}
