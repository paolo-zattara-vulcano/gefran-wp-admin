<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Framework\Api;

use RuntimeException;

/**
 * Interface for all content relationship meta API implementations.
 */
interface ContentRelationshipMetaInterface
{
    /**
     * Updates or creates(if doesn't exist) the relationship meta with given arguments.
     *
     * @param int $relationshipId The Relationship ID.
     * @param string $metaKey The meta key.
     * @param string $metaValue The meta value.
     * @throws RuntimeException if problem creating.
     */
    public function updateRelationshipMeta(int $relationshipId, string $metaKey, string $metaValue): void;

    /**
     * Gets the relationship meta value with given ID and meta key.
     *
     * @param int $relationshipId The Relationship ID.
     * @param string $metaKey The meta key.
     * @return string The meta value.
     */
    public function relationshipMetaValue(int $relationshipId, string $metaKey): string;

    /**
     * Gets the relationship meta value for given post ID and meta key.
     *
     * @param int $postId The post ID.
     * @param string $metaKey The meta key.
     * @return string The meta value.
     * @throws RuntimeException if problem getting.
     */
    public function relationshipMetaValueByPostId(int $postId, string $metaKey): string;

    /**
     * Deletes the relationship meta by given relationship ID.
     *
     * @param int $relationshipId The Relationship ID.
     * @return bool true if the relationship meta is deleted, otherwise false.
     * @throws RuntimeException if problem deleting.
     */
    public function deleteRelationshipMeta(int $relationshipId): bool;
}
