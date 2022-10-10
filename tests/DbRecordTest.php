<?php
use PHPUnit\Framework\TestCase;
use piko\Db;
use piko\Piko;

class DbRecordTest extends TestCase
{
    protected function setUp(): void
    {
        $db = new Db(['dsn' => 'sqlite::memory:']);
        Piko::set('db', $db);

        $query = <<<EOL
CREATE TABLE contact (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT,
  firstname TEXT,
  lastname TEXT,
  `order` INTEGER
)
EOL;
        $db->exec($query);
    }

    protected function tearDown(): void
    {
        $db = Piko::get('db');

        if ($db instanceof PDO) {
            $db = null;
        }

        Piko::set('db', null);
    }

    protected function createContact()
    {
        $contact = new Contact();
        $contact->firstname = 'Sylvain';
        $contact->lastname = 'Philip';
        $contact->order = 1; // order is a reserved word
        $contact->save();

        return $contact;
    }

    public function testWithNullDb()
    {
        Piko::set('db', null);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/No db instance found/');
        new Contact();
    }

    public function testWithWrongDb()
    {
        Piko::set('db', new DateTime());
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Db must be instance of \PDO.');
        new Contact();
    }

    public function testCreate()
    {
        $contact = $this->createContact();
        $this->assertEquals(1, $contact->id);
        $this->assertEquals('Sylvain', $contact->firstname);
        $this->assertEquals('Philip', $contact->lastname);
    }

    public function testLoadWithWrongPrimaryKey()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/no such column: contact_id/');
        new Contact2(1);
    }

    public function testWrongColumnAccess()
    {
        $contact = $this->createContact();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('email is not in the table schema.');
        $contact->email;
    }

    public function testIsset()
    {
        $contact = $this->createContact();
        $this->assertTrue(isset($contact->order));
        $this->assertFalse(isset($contact->email));
    }

    public function testUnset()
    {
        $contact = $this->createContact();
        unset($contact->order);
        $this->assertFalse(isset($contact->order));
        $this->assertNull($contact->order);
    }

    public function testUpdate()
    {
        $this->createContact();
        $contact = new Contact(1);

        $this->assertEquals('Sylvain', $contact->firstname);

        $contact->firstname .= ' updated';
        $contact->save();

        $contact = new Contact(1);
        $this->assertEquals('Sylvain updated', $contact->firstname);
    }

    public function testBeforeSave()
    {
        $contact = $this->createContact();
        $contact->on('beforeSave', function($instance, $insert) {
            $instance->name = $instance->firstname . ' ' . $instance->lastname;
        });
        $this->assertTrue($contact->save());
        $this->assertEquals('Sylvain Philip', $contact->name);
    }

    public function testBeforeSaveFalse()
    {
        $contact = $this->createContact();
        $contact->on('beforeSave', function($insert) use($contact) {
            return false;
        });
        $this->assertFalse($contact->save());
    }

    public function testDelete()
    {
        $contact = $this->createContact();
        $this->assertEquals(1, $contact->id);
        $contact->delete();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error while trying to load item 1');
        $contact = new Contact(1);
    }

    public function testDeleteNotLoaded()
    {
        $contact = new Contact();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Item cannot be delete because it is not loaded.');
        $contact->delete();
    }

    public function testBeforeDelete()
    {
        $contact = $this->createContact();
        $contact->on('beforeDelete', function($instance) {
            if ($instance->firstname == 'Sylvain') {
                return false;
            }
            return true;
        });

        $this->assertFalse($contact->delete());
    }
}

class Contact extends \piko\DbRecord
{
    protected $tableName = 'contact';

    protected $schema = [
        'id'        => self::TYPE_INT,
        'name'      => self::TYPE_STRING,
        'firstname' => self::TYPE_STRING,
        'lastname'  => self::TYPE_STRING,
        'order'     =>  self::TYPE_INT
    ];
}

class Contact2 extends \piko\DbRecord
{
    protected $tableName = 'contact';
    protected $primaryKey = 'contact_id';

    protected $schema = [
        'id'        => self::TYPE_INT,
        'name'      => self::TYPE_STRING,
        'firstname' => self::TYPE_STRING,
        'lastname'  => self::TYPE_STRING,
        'order'     =>  self::TYPE_INT
    ];
}
