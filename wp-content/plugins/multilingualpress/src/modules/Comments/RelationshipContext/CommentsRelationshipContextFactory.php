<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\Comments\RelationshipContext;

class CommentsRelationshipContextFactory implements CommentsRelationshipContextFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createCommentsRelationshipContext(
        int $sourceSiteId,
        int $remoteSiteId,
        int $sourceCommentId,
        int $remoteCommentId
    ): CommentsRelationshipContextInterface {

        return new CommentsRelationshipContext(
            [
                CommentsRelationshipContext::SOURCE_SITE_ID => $sourceSiteId,
                CommentsRelationshipContext::SOURCE_COMMENT_ID => $sourceCommentId,
                CommentsRelationshipContext::REMOTE_SITE_ID => $remoteSiteId,
                CommentsRelationshipContext::REMOTE_COMMENT_ID => $remoteCommentId,
            ]
        );
    }
}
