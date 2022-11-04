<?php
use PHPUnit\Framework\TestCase;

use Piko\DbRecord\Event\BeforeDeleteEvent;
use Piko\DbRecord\Event\BeforeSaveEvent;

class DbRecordTest extends TestCase
{
    protected $db;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:');

        $query = <<<EOL
CREATE TABLE contact (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT,
  firstname TEXT,
  lastname TEXT,
  `order` INTEGER
)
EOL;
        $this->db->exec($query);
    }

    protected function tearDown(): void
    {
        $this->db = null;
    }

    protected function createContact()
    {
        $contact = new Contact($this->db);
        $contact->firstname = 'Sylvain';
        $contact->lastname = 'Philip';
        $contact->order = 1; // order is a reserved word
        $contact->save();

        return $contact;
    }

    public function testWithNullDb()
    {
        $this->expectException(TypeError::class);
        new Contact(null);
    }

    public function testWithWrongDb()
    {
        $this->expectException(TypeError::class);
        new Contact(new DateTime());
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
        (new Contact2($this->db))->load(1);
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
        $contact = (new Contact($this->db))->load(1);
        $this->assertEquals('Sylvain', $contact->firstname);

        $contact->firstname .= ' updated';
        $contact->save();

        $contact = (new Contact($this->db))->load(1);
        $this->assertEquals('Sylvain updated', $contact->firstname);
    }

    public function testBeforeSave()
    {
        $contact = $this->createContact();
        $contact->on(BeforeSaveEvent::class, function(BeforeSaveEvent $event) {
            $event->record->name = $event->record->firstname . ' ' . $event->record->lastname;
        });
        $this->assertTrue($contact->save());
        $this->assertEquals('Sylvain Philip', $contact->name);
    }

    public function testBeforeSaveFalse()
    {
        $contact = $this->createContact();
        $contact->on(BeforeSaveEvent::class, function(BeforeSaveEvent $event) {
            $event->isValid = false;
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
        $contact = (new Contact($this->db))->load(1);
    }

    public function testDeleteNotLoaded()
    {
        $contact = new Contact($this->db);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Item cannot be delete because it is not loaded.');
        $contact->delete();
    }

    public function testBeforeDelete()
    {
        $contact = $this->createContact();
        $contact->on(BeforeDeleteEvent::class, function(BeforeDeleteEvent $event) {
            if ($event->record->firstname == 'Sylvain') {
                $event->isValid = false;
            }
        });

        $this->assertFalse($contact->delete());
    }

    public function testModelValidation()
    {
        $model = new Contact($this->db);

        $this->assertFalse($model->isValid());

        $errors = $model->getErrors();

        $this->assertArrayHasKey('firstname', $errors);
        $this->assertArrayHasKey('lastname', $errors);

        $model = new Contact($this->db);

        $model->firstname = 'John';
        $model->lastname = 'Lennon';

        $this->assertTrue($model->isValid());
    }

    public function testModelBind()
    {
        $model = new Contact($this->db);

        $data = [
            'id' => 1,
            'name' => 'John Lennon',
            'firstname' => 'John',
            'lastname' => 'Lennon',
            'order' => 1,
        ];

        $model->bind($data);
        $this->assertEquals($data, $model->toArray());
    }
}

class Contact extends \Piko\DbRecord
{
    protected $tableName = 'contact';

    protected $schema = [
        'id'        => self::TYPE_INT,
        'name'      => self::TYPE_STRING,
        'firstname' => self::TYPE_STRING,
        'lastname'  => self::TYPE_STRING,
        'order'     =>  self::TYPE_INT
    ];

    protected function validate(): void
    {
        if (empty($this->firstname)) {
            $this->setError('firstname', 'First name is required');
        }

        if (empty($this->lastname)) {
            $this->setError('lastname', 'Last name is required');
        }
    }
}

class Contact2 extends \Piko\DbRecord
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
