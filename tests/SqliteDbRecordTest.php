<?php
use Piko\Tests\AbstractTestDbRecord;


class SQLiteDbRecordTest extends AbstractTestDbRecord
{
    public static function setUpBeforeClass(): void
    {
        self::$db = new PDO('sqlite::memory:');

        $query = <<<EOL
CREATE TABLE contact (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  firstname TEXT,
  lastname TEXT,
  `order` INTEGER,
  active INTEGER DEFAULT 0,
  active2 INTEGER DEFAULT 1,
  income REAL DEFAULT 0
)
EOL;
        self::$db->exec($query);
    }

    public static function tearDownAfterClass(): void
    {
        self::$db = null;
    }

    protected function setUp(): void
    {
        self::$db->exec('DELETE FROM contact');
        self::$db->exec("DELETE FROM SQLITE_SEQUENCE WHERE name='contact'"); // Reset primaryu key
    }
}
