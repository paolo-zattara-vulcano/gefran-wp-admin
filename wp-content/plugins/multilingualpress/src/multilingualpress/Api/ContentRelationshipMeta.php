<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Api;

use Inpsyde\MultilingualPress\Database\Table\RelationshipMetaTable;
use Inpsyde\MultilingualPress\Framework\Api\ContentRelations;
use Inpsyde\MultilingualPress\Framework\Api\ContentRelationshipMetaInterface;
use Inpsyde\MultilingualPress\Framework\Database\Exception\NonexistentTable;
use wpdb;

class ContentRelationshipMeta implements ContentRelationshipMetaInterface
{
    /**
     * @var wpdb
     */
    protected $wpdb;

    /**
     * @var RelationshipMetaTable
     */
    protected $relationshipMetaTable;

    /**
     * @var ContentRelations
     */
    protected $contentRelations;

    public function __construct(
        wpdb $wpdb,
        RelationshipMetaTable $relationshipMetaTable,
        ContentRelations $contentRelations
    ) {

        $this->wpdb = $wpdb;
        $this->relationshipMetaTable = $relationshipMetaTable;
        $this->contentRelations = $contentRelations;
    }

    /**
     * @inheritDoc
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
     */
    public function updateRelationshipMeta(int $relationshipId, string $metaKey, $metaValue): void
    {
        // phpcs:enable

        $tableName = $this->relationshipMetaTable->name();

        if (!$this->relationshipMetaTable->exists()) {
            throw new NonexistentTable(__FUNCTION__, $tableName);
        }

        $colRelId = RelationshipMetaTable::COLUMN_RELATIONSHIP_ID;
        $colMetaKey = RelationshipMetaTable::COLUMN_META_KEY;
        $colMetaValue = RelationshipMetaTable::COLUMN_META_VALUE;

        $query = sprintf(
            'INSERT INTO %1$s (%2$s, %3$s, %4$s) VALUES (%%d, %%s, %%s) ON DUPLICATE KEY UPDATE %4$s = %%s',
            $tableName,
            $colRelId,
            $colMetaKey,
            $colMetaValue
        );

        //phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
        //phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $this->wpdb->query(
            $this->wpdb->prepare(
                $query,
                [$relationshipId, $metaKey, $metaValue, $metaValue]
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function deleteRelationshipMeta(int $relationshipId): bool
    {
        // phpcs:enable

        $tableName = $this->relationshipMetaTable->name();

        if (!$this->relationshipMetaTable->exists()) {
            throw new NonexistentTable(__FUNCTION__, $tableName);
        }

        return $this->wpdb->delete(
            $tableName,
            [RelationshipMetaTable::COLUMN_RELATIONSHIP_ID => $relationshipId],
            '%d'
        );
    }

    /**
     * @inheritDoc
     */
    public function relationshipMetaValue(int $relationshipId, string $metaKey): string
    {
        $tableName = $this->relationshipMetaTable->name();

        if (!$this->relationshipMetaTable->exists()) {
            throw new NonexistentTable(__FUNCTION__, $tableName);
        }

        $colRelId = RelationshipMetaTable::COLUMN_RELATIONSHIP_ID;
        $colMetaKey = RelationshipMetaTable::COLUMN_META_KEY;
        $colMetaValue = RelationshipMetaTable::COLUMN_META_VALUE;

        //phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
        //phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT {$colMetaValue} FROM {$tableName} WHERE {$colRelId} = %d AND {$colMetaKey} = %s",
                $relationshipId,
                $metaKey
            )
        ) ?? '';
        // phpcs:enable
    }

    /**
     * @inheritDoc
     */
    public function relationshipMetaValueByPostId(int $postId, string $metaKey): string
    {
        $relationshipId = $this->contentRelations->relationshipId(
            [get_current_blog_id() => $postId],
            ContentRelations::CONTENT_TYPE_POST
        );

        return $this->relationshipMetaValue($relationshipId, $metaKey);
    }
}
