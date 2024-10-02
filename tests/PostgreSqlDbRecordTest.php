<?php
use Piko\Tests\AbstractTestDbRecord;

class PostgreSqlDbRecordTest extends AbstractTestDbRecord
{
    public static function setUpBeforeClass(): void
    {
        try {
            $db = new PDO(
                'pgsql:host=' . $_ENV['POSTGRESQL_HOST'],
                $_ENV['POSTGRESQL_USER'],
                $_ENV['POSTGRESQL_PASSWORD']
            );

            // Check if the database already exists
            $result = $db->query("SELECT 1 FROM pg_database WHERE datname='test'");

            if ($result->fetchColumn() === false) {
                // Database does not exist, create it
                $db->exec('CREATE DATABASE test');
            }
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }

        self::$db = new PDO(
            'pgsql:host=' . $_ENV['POSTGRESQL_HOST'] . ';dbname=test',
            $_ENV['POSTGRESQL_USER'],
            $_ENV['POSTGRESQL_PASSWORD']
        );

        $query = <<<EOL
CREATE TABLE IF NOT EXISTS contact (
  id SERIAL PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  firstname VARCHAR(255),
  lastname VARCHAR(255),
  "order" INT,
  active BOOLEAN DEFAULT FALSE,
  active2 BOOLEAN DEFAULT TRUE,
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
        self::$db->exec('TRUNCATE TABLE contact RESTART IDENTITY');
    }
}
