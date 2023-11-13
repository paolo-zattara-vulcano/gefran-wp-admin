<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\Comments\RelationshipContext;

use RuntimeException;

/**
 * Can save the relationship for comments.
 */
interface CommentRelationSaveHelperInterface
{
    /**
     * Relates the comments of given relationship context.
     *
     * @param CommentsRelationshipContextInterface $context
     * @throws RuntimeException If problem relating.
     */
    public function relateComments(CommentsRelationshipContextInterface $context): void;

    /**
     * Disconnects the comments of given relationship context.
     *
     * @param CommentsRelationshipContextInterface $context
     * @throws RuntimeException If problem disconnecting.
     */
    public function disconnectComments(CommentsRelationshipContextInterface $context): void;
}
