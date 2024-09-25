# Piko Db Record

[![build](https://github.com/piko-framework/db-record/actions/workflows/php.yml/badge.svg)](https://github.com/piko-framework/db-record/actions/workflows/php.yml)
[![Coverage Status](https://coveralls.io/repos/github/piko-framework/db-record/badge.svg?branch=main)](https://coveralls.io/github/piko-framework/db-record?branch=main)

Piko Db Record is a lightweight Active Record implementation built on top of PDO.

It has been tested and works with the following databases:

- SQLite
- MySQL
- PostgreSQL
- MSSQL

## Installation

It's recommended that you use Composer to install Piko Db Record.

```bash
composer require piko/db-record
```

## Documentation

https://piko-framework.github.io/docs/db-record.html

## Usage

First, ensure you have the necessary autoloading in place:

```php
require 'vendor/autoload.php';
```

### Define Your Model

Use the `Piko\DbRecord`, `Piko\DbRecord\TableAttribute`, and `Piko\DbRecord\FieldAttribute` classes to define your model. For example:

```php
use Piko\DbRecord;
use Piko\DbRecord\TableAttribute;
use Piko\DbRecord\FieldAttribute;

#[TableAttribute(tableName:'contact')]
class Contact extends DbRecord
{
    #[FieldAttribute(primaryKey: true)]
    public ?int $id = null;

    #[FieldAttribute]
    public $name = null;

    #[FieldAttribute]
    public ?int $order = null;
}

```

### Setup Database Connection

Create a new PDO instance and set up your database schema:

```php
// See https://www.php.net/manual/en/class.pdo.php
$db = new PDO('sqlite::memory:');

$query = <<<EOL
CREATE TABLE contact (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  `order` INTEGER
)
EOL;

$db->exec($query);
```

### Perform CRUD Operations

#### Create

Create a new record and save it to the database:

```php
$contact = new Contact($db);
$contact->name = 'John';
$contact->order = 1;
$contact->save();

echo "Contact id : {$contact->id}"; // Contact id : 1
```

#### Read

Retrieve records from the database:

```php
$st = $db->prepare('SELECT * FROM contact');
$st->execute();
$rows = $st->fetchAll(PDO::FETCH_CLASS, Contact::class, [$db]);

print_r($rows); // Array ([0] => Contact Object(...))

// Load a single record by primary key:
$contact = (new Contact($db))->load(1);

var_dump($contact->name); // John
```

#### Delete

Delete a record from the database:

```php

$contact->delete();
print_r($st->fetchAll()); // Array()
```

## Support

If you encounter any issues or have questions, feel free to open an issue on GitHub.
