# Piko Db

[![build](https://github.com/piko-framework/db/actions/workflows/php.yml/badge.svg)](https://github.com/piko-framework/db/actions/workflows/php.yml)
[![Coverage Status](https://coveralls.io/repos/github/piko-framework/db/badge.svg?branch=main)](https://coveralls.io/github/piko-framework/db?branch=main)

A lightweight Active Record implementation and Data Access Object built on top of PDO.

# Installation

It's recommended that you use Composer to install Piko Db.

```bash
composer require piko/db
```

# Usage

```php
require 'vendor/autoload.php';

use piko\Db;
use piko\DbRecord;
use piko\Piko;

class Contact extends DbRecord
{
    protected $tableName = 'contact';

    protected $schema = [
        'id'        => self::TYPE_INT,
        'name'      => self::TYPE_STRING,
        'order'     =>  self::TYPE_INT
    ];
}

// Db is a proxy to PDO
// See https://www.php.net/manual/en/class.pdo.php
$db = new Db(['dsn' => 'sqlite::memory:']);
Piko::set('db', $db);

$query = <<<EOL
CREATE TABLE contact (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  `order` INTEGER
)
EOL;

$db->exec($query);

$contact = new Contact();
$contact->name = 'John';
$contact->order = 1;
$contact->save();

var_dump($contact->id); // 1

$st = $db->prepare('SELECT * FROM contact');
$st->execute();
print_r($st->fetchAll());

$contact = new Contact(1);
var_dump($contact->name); // John

$contact->delete();

print_r($st->fetchAll());

```
