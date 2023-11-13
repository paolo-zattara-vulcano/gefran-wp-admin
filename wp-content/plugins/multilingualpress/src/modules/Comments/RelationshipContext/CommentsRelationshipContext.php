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

namespace Inpsyde\MultilingualPress\Module\Comments\RelationshipContext;

use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelper;
use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelperInterface;
use stdClass;
use WP_Comment;

use function Inpsyde\MultilingualPress\combineAtts;
use function Inpsyde\MultilingualPress\translationIds;

class CommentsRelationshipContext implements CommentsRelationshipContextInterface
{
    public const REMOTE_COMMENT_ID = 'remote_comment_id';
    public const REMOTE_POST_ID = 'remote_post_id';
    public const REMOTE_SITE_ID = 'remote_site_id';
    public const SOURCE_COMMENT_ID = 'source_comment_id';
    public const SOURCE_SITE_ID = 'source_site_id';

    protected const DEFAULTS = [
        self::REMOTE_COMMENT_ID => 0,
        self::REMOTE_POST_ID => 0,
        self::REMOTE_SITE_ID => 0,
        self::SOURCE_COMMENT_ID => 0,
        self::SOURCE_SITE_ID => 0,
    ];

    /**
     * @var WP_Comment[]
     */
    protected $comments = [];

    /**
     * @var array
     */
    protected $data;

    /**
     * Returns a new context object, instantiated according to the data in the given context object
     * and the array.
     *
     * @param CommentsRelationshipContext $context
     * @param array $data
     * @return CommentsRelationshipContext
     */
    public static function fromExistingAndData(
        CommentsRelationshipContext $context,
        array $data
    ): CommentsRelationshipContext {

        $instance = new static();
        $instance->data = combineAtts($context->data, $data);

        if (
            !array_key_exists(self::SOURCE_COMMENT_ID, $data)
            && array_key_exists('source', $context->comments)
        ) {
            $instance->comments['source'] = $context->comments['source'];
        }

        if (
            !array_key_exists(self::REMOTE_COMMENT_ID, $data)
            && array_key_exists('remote', $context->comments)
        ) {
            $instance->comments['remote'] = $context->comments['remote'];
        }

        return $instance;
    }

    public function __construct(array $data = [])
    {
        if (!is_array($this->data)) {
            $this->data = combineAtts(self::DEFAULTS, $data);
        }
    }

    /**
     * @inheritDoc
     */
    public function remoteCommentId(): int
    {
        return (int)$this->data[static::REMOTE_COMMENT_ID];
    }

    /**
     * @inheritDoc
     */
    public function remoteComment(): ?WP_Comment
    {
        return $this->commentByType($this->remoteSiteId(), 'remote');
    }

    /**
     * @inheritDoc
     */
    public function remotePostId(): ?int
    {
        $sourceCommentPostId = (int)$this->sourceComment()->comment_post_ID;
        $translations = translationIds($sourceCommentPostId, 'post', $this->sourceSiteId());

        if (empty($translations)) {
            return null;
        }

        return $translations[$this->remoteSiteId()] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function remoteSiteId(): int
    {
        return (int)$this->data[static::REMOTE_SITE_ID];
    }

    /**
     * @inheritDoc
     */
    public function remoteCommentParentId(): ?int
    {
        $sourceCommentPostId = (int)$this->sourceComment()->comment_parent;
        $translations = translationIds($sourceCommentPostId, 'comment', $this->sourceSiteId());

        if (empty($translations)) {
            return null;
        }

        return $translations[$this->remoteSiteId()] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function hasRemoteComment(): bool
    {
        return $this->remoteComment() instanceof WP_Comment;
    }

    /**
     * @inheritDoc
     */
    public function sourceCommentId(): int
    {
        return (int)$this->data[static::SOURCE_COMMENT_ID];
    }

    /**
     * @inheritDoc
     */
    public function sourceSiteId(): int
    {
        return (int)$this->data[static::SOURCE_SITE_ID];
    }

    /**
     * @inheritDoc
     */
    public function sourceComment(): WP_Comment
    {
        return $this->commentByType($this->sourceSiteId(), 'source') ?: new WP_Comment(new stdClass());
    }

    /**
     * Returns the comment object from given site by given type.
     *
     * @param int $siteId The site ID.
     * @param string $type The type: source or remote.
     * @return WP_Comment|null
     */
    protected function commentByType(int $siteId, string $type): ?WP_Comment
    {
        if (!array_key_exists($type, $this->comments)) {
            if (!$siteId) {
                $this->comments[$type] = null;
                return null;
            }

            $commentId = $this->{"{$type}CommentId"}();
            if (!$commentId) {
                $this->comments[$type] = null;
                return null;
            }

            switch_to_blog($siteId);
            $this->comments[$type] = get_comment($commentId);
            restore_current_blog();
        }

        return $this->comments[$type] ?: null;
    }

    /**
     * @inheritDoc
     */
    public function renderFields(MetaboxFieldsHelperInterface $helper): void
    {
        $baseName = $helper->fieldName('relation_context');
        $baseId = $helper->fieldId('relation_context');

        $fields = [
            CommentsRelationshipContext::SOURCE_SITE_ID => [$this, 'sourceSiteId'],
            CommentsRelationshipContext::SOURCE_COMMENT_ID => [$this, 'sourceCommentId'],
            CommentsRelationshipContext::REMOTE_SITE_ID => [$this, 'remoteSiteId'],
            CommentsRelationshipContext::REMOTE_COMMENT_ID => [$this, 'remoteCommentId'],
        ];

        foreach ($fields as $key => $callback) {
            ?>
            <input
                type="hidden"
                class="relationship-context-fields"
                name="<?= esc_attr("{$baseName}[{$key}]") ?>"
                id="<?= esc_attr("{$baseId}-{$key}") ?>"
                value="<?= esc_attr((string)$callback()) ?>">
            <?php
        }
    }
}
