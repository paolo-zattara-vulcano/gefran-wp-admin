<?php

# -*- coding: utf-8 -*-
/*
 * This file is part of the MultilingualPress package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Framework\Database;

/**
 * Table duplicator implementation using the WordPress database object.
 */
class TableDuplicator
{
    /**
     * @var \wpdb
     */
    private $db;

    /**
     * @param \wpdb $db
     */
    public function __construct(\wpdb $db)
    {
        $this->db = $db;
    }

    /**
     * Creates a new table that is an exact duplicate of an existing table.
     *
     * @param string $existingTableName
     * @param string $newTableName
     * @return bool
     */
    public function duplicate(string $existingTableName, string $newTableName): bool
    {
        $this->db->query("DROP TABLE IF EXISTS {$newTableName}");

        return (bool)$this->db->query("CREATE TABLE {$newTableName} LIKE {$existingTableName}");
    }
}
