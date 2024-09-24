<?php
use Piko\Tests\Contact;
use Piko\Tests\Contact2;
use Piko\Tests\ContactLegacy;
use PHPUnit\Framework\TestCase;
use Piko\DbRecord\Event\BeforeSaveEvent;
use Piko\DbRecord\Event\BeforeDeleteEvent;
use PHPUnit\Framework\Attributes\DataProvider;

class DbRecordTest extends TestCase
{
    protected $db;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:');

        $query = <<<EOL
CREATE TABLE contact (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
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

    protected function createContact($className)
    {
        $contact = new $className($this->db);
        $contact->name = 'Toto';
        $contact->firstname = 'Sylvain';
        $contact->lastname = 'Philip';
        $contact->order = 1; // order is a reserved word
        $contact->save();

        return $contact;
    }

    public static function contactProvider()
    {
        return [
            [Contact::class],
            [ContactLegacy::class]
        ];
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

    #[DataProvider('contactProvider')]
    public function testCreate($className)
    {
        $contact = $this->createContact($className);
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

    #[DataProvider('contactProvider')]
    public function testWrongColumnAccess($className)
    {
        $contact = $this->createContact($className);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('email is not in the table schema.');
        $contact->email;
    }

    #[DataProvider('contactProvider')]
    public function testIsset($className)
    {
        $contact = $this->createContact($className);
        $this->assertTrue(isset($contact->order));
        $this->assertFalse(isset($contact->email));
    }

    #[DataProvider('contactProvider')]
    public function testUnset($className)
    {
        $contact = $this->createContact($className);
        unset($contact->order);
        $this->assertFalse(isset($contact->order));
        $this->assertNull($contact->order);
    }

    #[DataProvider('contactProvider')]
    public function testUpdate($className)
    {
        $this->createContact($className);
        $contact = (new Contact($this->db))->load(1);
        $this->assertEquals('Sylvain', $contact->firstname);

        $contact->firstname .= ' updated';
        $contact->save();

        $contact = (new Contact($this->db))->load(1);
        $this->assertEquals('Sylvain updated', $contact->firstname);
    }

    #[DataProvider('contactProvider')]
    public function testBeforeSave($className)
    {
        $contact = $this->createContact($className);
        $contact->on(BeforeSaveEvent::class, function(BeforeSaveEvent $event) {
            $event->record->name = $event->record->firstname . ' ' . $event->record->lastname;
        });
        $this->assertTrue($contact->save());
        $this->assertEquals('Sylvain Philip', $contact->name);
    }

    #[DataProvider('contactProvider')]
    public function testBeforeSaveFalse($className)
    {
        $contact = $this->createContact($className);
        $contact->on(BeforeSaveEvent::class, function(BeforeSaveEvent $event) {
            $event->isValid = false;
        });
        $this->assertFalse($contact->save());
    }

    #[DataProvider('contactProvider')]
    public function testDelete($className)
    {
        $contact = $this->createContact($className);
        $this->assertEquals(1, $contact->id);
        $contact->delete();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error while trying to load item 1');
        $contact = (new Contact($this->db))->load(1);
    }

    #[DataProvider('contactProvider')]
    public function testDeleteNotLoaded($className)
    {
        $contact = new $className($this->db);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Item cannot be delete because it is not loaded.');
        $contact->delete();
    }

    #[DataProvider('contactProvider')]
    public function testBeforeDelete($className)
    {
        $contact = $this->createContact($className);
        $contact->on(BeforeDeleteEvent::class, function(BeforeDeleteEvent $event) {
            if ($event->record->firstname == 'Sylvain') {
                $event->isValid = false;
            }
        });

        $this->assertFalse($contact->delete());
    }

    #[DataProvider('contactProvider')]
    public function testModelValidation($className)
    {
        $model = new $className($this->db);

        $this->assertFalse($model->isValid());

        $errors = $model->getErrors();

        $this->assertArrayHasKey('firstname', $errors);
        $this->assertArrayHasKey('lastname', $errors);

        $model = new $className($this->db);

        $model->firstname = 'John';
        $model->lastname = 'Lennon';

        $this->assertTrue($model->isValid());
    }

    #[DataProvider('contactProvider')]
    public function testModelBind($className)
    {
        $model = new $className($this->db);

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
