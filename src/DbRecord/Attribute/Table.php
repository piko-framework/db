<?php

/**
 * This file is part of Piko DbRecord - Web micro framework
 *
 * @copyright 2019-2024 Sylvain PHILIP
 * @license LGPL-3.0; see LICENSE.txt
 * @link https://github.com/piko-framework/db-record
 */

declare(strict_types=1);

namespace Piko\DbRecord\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
/**
 * The Table class is used to associate a PHP class with a specific database table.
 *
 * This attribute can be applied to a class to define the corresponding database table name.
 * It is part of the Piko DbRecord framework, which aims to simplify database interactions
 * in PHP projects by providing a straightforward and efficient ORM-like solution.
 *
 * Usage example:
 *
 * ```php
 * #[Table(name: 'users')]
 * class User {
 *     // Class implementation
 * }
 * ```
 *
 * @package Piko\DbRecord
 * @author Sylvain PHILIP <contact@sphilip.com>
 */
class Table
{
    /**
     * Constructor for TableAttribute class.
     *
     * @param string $name The name of the database table associated with the class.
     */
    public function __construct(public string $name)
    {
    }
}
