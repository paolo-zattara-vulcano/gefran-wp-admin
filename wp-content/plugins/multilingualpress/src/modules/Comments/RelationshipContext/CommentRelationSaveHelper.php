<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\Comments\RelationshipContext;

use Inpsyde\MultilingualPress\Framework\Api\ContentRelations;
use Inpsyde\MultilingualPress\Framework\Database\Exception\NonexistentTable;
use RuntimeException;

class CommentRelationSaveHelper implements CommentRelationSaveHelperInterface
{
    public const ACTION_BEFORE_SAVE_COMMENT_RELATIONS = 'multilingualpress.before_save_comment_relations';
    public const ACTION_AFTER_SAVED_COMMENTS_RELATIONS = 'multilingualpress.after_saved_comment_relations';

    /**
     * @var ContentRelations
     */
    private $contentRelations;

    public function __construct(ContentRelations $contentRelations)
    {
        $this->contentRelations = $contentRelations;
    }

    /**
     * @inheritDoc
     * @throws NonexistentTable
     */
    public function relateComments(CommentsRelationshipContextInterface $context): void
    {
        $sourceSiteId = $context->sourceSiteId();
        $sourceCommentId = $context->sourceCommentId();
        $remoteSiteId = $context->remoteSiteId();
        $remoteCommentId = $context->remoteCommentId();

        if ($sourceSiteId === $remoteSiteId) {
            return;
        }

        if (!$context->hasRemoteComment()) {
            throw new RuntimeException("The remote comment doesn't exist.");
        }

        $siteToCommentIdMap = [
            $sourceSiteId => $sourceCommentId,
            $remoteSiteId => $remoteCommentId,
        ];

        $relationshipId = $this->contentRelations->relationshipId(
            $siteToCommentIdMap,
            ContentRelations::CONTENT_TYPE_COMMENT,
            true
        );

        if (!$relationshipId) {
            throw new RuntimeException("Couldn't create the relationship.");
        }

        /**
         * Before save relations.
         *
         * @param CommentsRelationshipContextInterface $context The context of the relationship.
         * @param int $relationshipId The ID of the relation.
         */
        do_action(self::ACTION_BEFORE_SAVE_COMMENT_RELATIONS, $context, $relationshipId);

        foreach ($siteToCommentIdMap as $siteId => $commentId) {
            if (!$this->contentRelations->saveRelation($relationshipId, $siteId, $commentId)) {
                throw new RuntimeException("Couldn't save the relationship.");
            }
        }

        /**
         * After saved relations.
         *
         * @param CommentsRelationshipContextInterface $context The context of the relationship.
         * @param int $relationshipId The ID of the relation.
         */
        do_action(self::ACTION_AFTER_SAVED_COMMENTS_RELATIONS, $context, $relationshipId);
    }

    /**
     * @inheritDoc
     */
    public function disconnectComments(CommentsRelationshipContextInterface $context): void
    {
        $contentIds = [$context->remoteSiteId() => $context->remoteCommentId()];
        if (!$this->contentRelations->deleteRelation($contentIds, ContentRelations::CONTENT_TYPE_COMMENT)) {
            throw new RuntimeException("Couldn't save the relationship.");
        }
    }
}
