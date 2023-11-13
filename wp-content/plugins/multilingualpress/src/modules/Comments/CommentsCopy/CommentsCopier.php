<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\Comments\CommentsCopy;

use Inpsyde\MultilingualPress\Framework\Api\ContentRelations;
use Inpsyde\MultilingualPress\Framework\Database\Exception\NonexistentTable;
use Inpsyde\MultilingualPress\Module\Comments\RelationshipContext\CommentRelationSaveHelperInterface;
use Inpsyde\MultilingualPress\Module\Comments\RelationshipContext\CommentsRelationshipContextFactoryInterface;
use Inpsyde\MultilingualPress\Module\Comments\RelationshipContext\CommentsRelationshipContextInterface;
use RuntimeException;

use function Inpsyde\MultilingualPress\translationIds;

class CommentsCopier implements CommentsCopierInterface
{
    public const ACTION_AFTER_REMOTE_COMMENT_IS_INSERTED = 'multilingualpress.after_remote_comment_is_inserted';

    /**
     * @var CommentsRelationshipContextFactoryInterface
     */
    protected $relationshipContextFactory;

    /**
     * @var CommentRelationSaveHelperInterface
     */
    protected $commentRelationSaveHelper;

    public function __construct(
        CommentsRelationshipContextFactoryInterface $relationshipContextFactory,
        CommentRelationSaveHelperInterface $commentRelationSaveHelper
    ) {

        $this->relationshipContextFactory = $relationshipContextFactory;
        $this->commentRelationSaveHelper = $commentRelationSaveHelper;
    }

    /**
     * @inheritDoc
     * phpcs:disable Inpsyde.CodeQuality.NestingLevel.High
     */
    public function copyCommentsToSites(int $sourceSiteId, array $sourceCommentIds, array $remoteSiteIds): void
    {
        // phpcs:enable

        foreach ($sourceCommentIds as $commentId) {
            switch_to_blog($sourceSiteId);

            $commentData = get_comment($commentId, ARRAY_A);
            $sourceCommentPostId = (int)$commentData['comment_post_ID'] ?? 0;
            $sourceCommentParent = (int)$commentData['comment_parent'] ?? 0;

            restore_current_blog();

            if (!$sourceCommentPostId > 0) {
                continue;
            }

            foreach ($remoteSiteIds as $remoteSiteId) {
                if ($this->commentConnectionExistsInSite($commentId, $sourceSiteId, $remoteSiteId)) {
                    continue;
                }
                try {
                    $translations = translationIds($sourceCommentPostId, ContentRelations::CONTENT_TYPE_POST, $sourceSiteId);
                    $remotePostId = $translations[$remoteSiteId] ?? 0;
                    if (!$remotePostId > 0) {
                        continue;
                    }

                    $commentData['comment_post_ID'] = (string)$remotePostId;

                    if ($sourceCommentParent > 0) {
                        $commentData['comment_parent'] = $this->remoteCommentParent(
                            $sourceCommentParent,
                            $sourceSiteId,
                            $remoteSiteId
                        );
                    }

                    $insertedCommentId = $this->insertComment($commentData, $remoteSiteId);

                    $relationshipContext = $this->relationshipContextFactory->createCommentsRelationshipContext(
                        $sourceSiteId,
                        $remoteSiteId,
                        $commentId,
                        $insertedCommentId
                    );

                    $this->commentRelationSaveHelper->relateComments($relationshipContext);
                } catch (RuntimeException $exception) {
                    throw $exception;
                }


                /**
                 * Action after the remote comment is inserted.
                 *
                 * @param CommentsRelationshipContextInterface $relationshipContext
                 */
                do_action(self::ACTION_AFTER_REMOTE_COMMENT_IS_INSERTED, $relationshipContext);
            }
        }
    }


    /**
     * Inserts the given comment to the given site.
     *
     * @param array $comment A map of WP_Comment fields to values.
     * @param int $siteId The site ID.
     * @return int The inserted comment id.
     * @throws RuntimeException|NonexistentTable If problem inserting.
     */
    protected function insertComment(array $comment, int $siteId): int
    {
        switch_to_blog($siteId);

        $insertedCommentId = wp_insert_comment($comment);

        restore_current_blog();

        if (!$insertedCommentId > 0) {
            throw new RuntimeException('The comment is not inserted.');
        }

        return $insertedCommentId;
    }

    /**
     * Checks if the comment connection already exists in given remote site.
     *
     * @param int $commentId The comment ID.
     * @param int $sourceSiteId The source site ID.
     * @param int $remoteSiteId The remote site ID.
     * @return bool true if the comment connection exists, otherwise false.
     */
    protected function commentConnectionExistsInSite(int $commentId, int $sourceSiteId, int $remoteSiteId): bool
    {
        $translations = translationIds($commentId, ContentRelations::CONTENT_TYPE_COMMENT, $sourceSiteId);

        return array_key_exists($remoteSiteId, $translations);
    }

    /**
     * Returns the remote comment parent ID.
     *
     * @param int $commentParentId The source comment parent ID.
     * @param int $sourceSiteId The source site ID.
     * @param int $remoteSiteId The remote site ID.
     * @return int The remote comment parent ID.
     */
    protected function remoteCommentParent(int $commentParentId, int $sourceSiteId, int $remoteSiteId): int
    {
        $translations = translationIds($commentParentId, ContentRelations::CONTENT_TYPE_COMMENT, $sourceSiteId);

        return $translations[$remoteSiteId] ?? 0;
    }
}
