<?php

namespace Inpsyde\MultilingualPress\Module\Comments\RelationshipContext;

use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelperInterface;
use WP_Comment;

interface CommentsRelationshipContextInterface
{
    /**
     * The remote comment ID.
     *
     * @return int
     */
    public function remoteCommentId(): int;

    /**
     * The remote comment object.
     *
     * @return WP_Comment|null
     */
    public function remoteComment(): ?WP_Comment;

    /**
     * The remote post ID.
     *
     * @return int
     */
    public function remotePostId(): ?int;

    /**
     * The remote site ID.
     *
     * @return int
     */
    public function remoteSiteId(): int;

    /**
     * The remote comment parent comment ID.
     *
     * @return int
     */
    public function remoteCommentParentId(): ?int;

    /**
     * Returns whether the comment has connection.
     *
     * @return bool
     */
    public function hasRemoteComment(): bool;

    /**
     * The source comment ID.
     *
     * @return int
     */
    public function sourceCommentId(): int;

    /**
     * The source site ID.
     *
     * @return int
     */
    public function sourceSiteId(): int;

    /**
     * The source comment object.
     *
     * @return WP_Comment|null
     */
    public function sourceComment(): ?WP_Comment;

    /**
     * Print HTML fields for the relationship context.
     *
     * @param MetaboxFieldsHelperInterface $helper
     */
    public function renderFields(MetaboxFieldsHelperInterface $helper): void;
}
