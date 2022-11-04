# Piko Db

[![build](https://github.com/piko-framework/db-record/actions/workflows/php.yml/badge.svg)](https://github.com/piko-framework/db-record/actions/workflows/php.yml)
[![Coverage Status](https://coveralls.io/repos/github/piko-framework/db-record/badge.svg?branch=main)](https://coveralls.io/github/piko-framework/db-record?branch=main)

A lightweight Active Record implementation built on top of PDO.

# Installation

It's recommended that you use Composer to install Piko Db.

```bash
composer require piko/db-record
```

# Usage

```php
require 'vendor/autoload.php';

use Piko\DbRecord;

class Contact extends DbRecord
{
    protected $tableName = 'contact';

    protected $schema = [
        'id'        => self::TYPE_INT,
        'name'      => self::TYPE_STRING,
        'order'     =>  self::TYPE_INT
    ];
}

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

$contact = new Contact($db);
$contact->name = 'John';
$contact->order = 1;
$contact->save();

var_dump($contact->id); // 1

$st = $db->prepare('SELECT * FROM contact');
$st->execute();
$rows = $st->fetchAll(PDO::FETCH_CLASS, Contact::class, [$db]);

print_r($rows); // Array ([0] => Contact Object(...))

$contact = (new Contact($db))->load(1);

var_dump($contact->name); // John

$contact->delete();

print_r($st->fetchAll()); // Array()

```
