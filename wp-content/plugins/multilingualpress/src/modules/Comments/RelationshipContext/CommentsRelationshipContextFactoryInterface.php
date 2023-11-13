<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\Comments\RelationshipContext;

use RuntimeException;

/**
 * Can create relationship context for comments.
 */
interface CommentsRelationshipContextFactoryInterface
{
    /**
     * Creates new relationship context for comments.
     *
     * @param int $sourceSiteId The source site ID.
     * @param int $remoteSiteId The remote site ID.
     * @param int $sourceCommentId The source comment ID.
     * @param int $remoteCommentId The remote comment ID.
     * @return CommentsRelationshipContextInterface The new instance.
     * @throws RuntimeException If problem creating.
     */
    public function createCommentsRelationshipContext(
        int $sourceSiteId,
        int $remoteSiteId,
        int $sourceCommentId,
        int $remoteCommentId
    ): CommentsRelationshipContextInterface;
}
