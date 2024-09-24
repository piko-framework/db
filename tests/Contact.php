<?php
namespace Piko\Tests;

use Piko\DbRecord\TableAttribute;
use Piko\DbRecord\FieldAttribute;

#[TableAttribute(tableName: 'contact')]
class Contact extends \Piko\DbRecord
{
    #[FieldAttribute(primaryKey: true)]
    public ?int $id = null;

    #[FieldAttribute]
    public $name = null;

    #[FieldAttribute]
    public ?string $firstname = null;

    #[FieldAttribute]
    public ?string $lastname = null;

    #[FieldAttribute]
    public ?int $order = null;

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
