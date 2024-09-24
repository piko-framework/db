<?php

/**
 * This file is part of Piko DbRecord - Web micro framework
 *
 * @copyright 2019-2024 Sylvain PHILIP
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/piko-framework/db-record
 */

declare(strict_types=1);

namespace Piko\DbRecord;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
/**
 * Attribute class to define metadata for a database field.
 *
 * This attribute can be used to specify properties of database fields such as
 * whether the field is a primary key and what its name should be.
 *
 * Usage example:
 *
 * ```php
 * #[TableAttribute('users')]
 * class User {
 *     #[FieldAttribute(primaryKey: true, fieldName: 'id')]
 *     public int $id;
 *
 *     #[FieldAttribute(fieldName: 'username')]
 *     public string $username;
 *
 *     #[FieldAttribute(fieldName: 'email')]
 *     public string $email;
 *
 *     // Class implementation
 * }
 * ```
 *
 * @package Piko\DbRecord
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class FieldAttribute
{
    /**
     * Constructor for the FieldAttribute class.
     *
     * @param bool $primaryKey Indicates if the field is a primary key. Default is false.
     * @param string|null $fieldName The name of the field. Default is null.
     */
    public function __construct(public bool $primaryKey = false, public ?string $fieldName = null)
    {
    }
}
