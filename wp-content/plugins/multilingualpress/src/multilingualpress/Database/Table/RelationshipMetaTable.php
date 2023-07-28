<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Database\Table;

use Inpsyde\MultilingualPress\Framework\Database\Table;

class RelationshipMetaTable implements Table
{
    use TableTrait;

    public const COLUMN_RELATIONSHIP_ID = 'relationship_id';
    public const COLUMN_META_KEY = 'meta_key';
    public const COLUMN_META_VALUE = 'meta_value';

    /**
     * @var string
     */
    private $prefix;

    public function __construct(string $tablePrefix = '')
    {
        $this->prefix = $tablePrefix;
    }

    /**
     * @inheritdoc
     */
    public function columnsWithoutDefaultContent(): array
    {
        return [
            self::COLUMN_RELATIONSHIP_ID,
        ];
    }

    /**
     * @inheritdoc
     */
    public function defaultContentSql(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function keysSql(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function name(): string
    {
        return "{$this->prefix}mlp_relationship_meta";
    }

    /**
     * @inheritdoc
     */
    public function primaryKey(): string
    {
        return self::COLUMN_RELATIONSHIP_ID;
    }

    /**
     * @inheritdoc
     */
    public function schema(): array
    {
        return [
            self::COLUMN_RELATIONSHIP_ID => 'bigint(20) unsigned NOT NULL auto_increment',
            self::COLUMN_META_KEY => 'varchar(255) NULL',
            self::COLUMN_META_VALUE => 'longtext NULL',
        ];
    }
}
