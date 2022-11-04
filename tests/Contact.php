<?php
namespace Piko\Tests;

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