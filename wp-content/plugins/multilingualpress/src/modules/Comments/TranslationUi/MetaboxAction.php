<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\Comments\TranslationUi;

use Inpsyde\MultilingualPress\Framework\Admin\AdminNotice;
use Inpsyde\MultilingualPress\Framework\Admin\Metabox;
use Inpsyde\MultilingualPress\Framework\Admin\PersistentAdminNotices;
use Inpsyde\MultilingualPress\Framework\Http\Request;
use Inpsyde\MultilingualPress\Module\Comments\RelationshipContext\CommentRelationSaveHelperInterface;
use Inpsyde\MultilingualPress\Module\Comments\RelationshipContext\CommentsRelationshipContext;
use Inpsyde\MultilingualPress\Module\Comments\RelationshipContext\CommentsRelationshipContextInterface;
use Inpsyde\MultilingualPress\Module\Comments\TranslationUi\Field\CommentMetaboxField;
use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelperInterface;
use RuntimeException;

/**
 * @psalm-type FieldName = string
 * @psalm-type FieldValue = scalar
 */
class MetaboxAction implements Metabox\Action
{
    public const FILTER_TAXONOMIES_SLUGS_BEFORE_REMOVE = 'multilingualpress.taxonomies_slugs_before_remove';
    public const FILTER_NEW_RELATE_REMOTE_COMMENT_BEFORE_INSERT = 'multilingualpress.new_relate_remote_comment_before_insert';
    public const ACTION_METABOX_AFTER_RELATE_COMMENTS = 'multilingualpress.metabox_after_relate_comments';
    public const ACTION_METABOX_BEFORE_UPDATE_REMOTE_COMMENT = 'multilingualpress.metabox_before_update_remote_comment';
    public const ACTION_METABOX_AFTER_UPDATE_REMOTE_COMMENT = 'multilingualpress.metabox_after_update_remote_comment';

    /**
     * @var CommentMetaboxField[]
     */
    protected $metaboxFields;

    /**
     * @var MetaboxFieldsHelperInterface
     */
    private $fieldsHelper;

    /**
     * @var CommentsRelationshipContextInterface
     */
    private $relationshipContext;

    /**
     * @var CommentRelationSaveHelperInterface
     */
    protected $commentRelationSaveHelper;

    public function __construct(
        array $metaboxFields,
        MetaboxFieldsHelperInterface $fieldsHelper,
        CommentsRelationshipContextInterface $relationshipContext,
        CommentRelationSaveHelperInterface $commentRelationSaveHelper
    ) {

        $this->metaboxFields = $metaboxFields;
        $this->fieldsHelper = $fieldsHelper;
        $this->relationshipContext = $relationshipContext;
        $this->commentRelationSaveHelper = $commentRelationSaveHelper;
    }

    /**
     * @inheritdoc
     */
    public function save(Request $request, PersistentAdminNotices $notices): bool
    {
        $relationType = $this->fieldsHelper->fieldRequestValue($request, 'relationship');
        if (!$relationType) {
            return false;
        }

        $hasRemoteComment = $this->relationshipContext->hasRemoteComment();

        if (!$this->shouldSaveComment($relationType, $hasRemoteComment)) {
            return false;
        }

        $values = $this->allFieldsValues($request);
        $remoteComment = $this->createCommentData($values, $request);

        if (!$remoteComment) {
            return false;
        }

        /**
         * Performs an action before the remote comment has been updated.
         *
         * @param array $remoteComment A map of WP_comment properties to values.
         * @param CommentsRelationshipContextInterface $relationshipContext
         * @param string $relationType The relation type (existing, new, remove, leave).
         */
        do_action(self::ACTION_METABOX_BEFORE_UPDATE_REMOTE_COMMENT, $remoteComment, $this->relationshipContext, $relationType);

        if ($relationType === 'new') {

            /**
             * Allows to filter remote comment data before insert.
             *
             * @param array $remoteComment A map of WP_comment properties to values.
             * @param CommentsRelationshipContextInterface $relationshipContext
             * @param string $relationType The relation type (existing, new, remove, leave).
             */
            $remoteComment = (array)apply_filters(
                self::FILTER_NEW_RELATE_REMOTE_COMMENT_BEFORE_INSERT,
                $remoteComment,
                $this->relationshipContext,
                $relationType
            );
        }

        try {
            $remoteCommentId = $this->saveComment($remoteComment, $relationType);
            $remoteComment = get_comment($remoteCommentId);

            /**
             * Performs an action after the remote comment has been updated.
             *
             * @param array $remoteComment A map of WP_comment properties to values.
             * @param CommentsRelationshipContextInterface $relationshipContext
             * @param string $relationType The relation type (existing, new, remove, leave).
             */
            do_action(self::ACTION_METABOX_AFTER_UPDATE_REMOTE_COMMENT, $remoteComment, $this->relationshipContext, $relationType);

            $this->relationshipContext = CommentsRelationshipContext::fromExistingAndData(
                $this->relationshipContext,
                [CommentsRelationshipContext::REMOTE_COMMENT_ID => $remoteCommentId]
            );

            $this->commentRelationSaveHelper->relateComments($this->relationshipContext);

            /**
             * Perform an action after the comment relations have been updated.
             *
             * @param CommentsRelationshipContextInterface $relationshipContext
             * @param Request $request
             * @param PersistentAdminNotices $notices
             */
            do_action(self::ACTION_METABOX_AFTER_RELATE_COMMENTS, $this->relationshipContext, $request, $notices);
        } catch (RuntimeException $exception) {
            $notices->add(AdminNotice::error($exception->getMessage()));
            return false;
        }

        return true;
    }

    /**
     * Checks if the relationship should be updated based on given params.
     *
     * @param string $relationType The relation type (existing, new, remove, leave).
     * @param bool $hasRemoteComment True if connection exists, otherwise false.
     * @return bool true if relationship should be updated, otherwise false.
     */
    protected function shouldSaveComment(string $relationType, bool $hasRemoteComment): bool
    {
        if ($relationType !== 'new' && $relationType !== 'leave') {
            return false;
        }

        if (($relationType === 'new' && $hasRemoteComment) || ($relationType === 'leave' && !$hasRemoteComment)) {
            return false;
        }

        return true;
    }

    /**
     * Returns the map of field keys to values from given request.
     *
     * @param Request $request
     * @return array<string, scalar> The map of field keys to values.
     * @psalm-return array<FieldName, FieldValue>
     */
    protected function allFieldsValues(Request $request): array
    {
        $fields = [];
        foreach ($this->metaboxFields as $field) {
            $fields[$field->key()] = $this->fieldsHelper->fieldRequestValue($request, $field->key());
        }

        return $fields;
    }

    /**
     * Creates the remote comment data for given request.
     *
     * @param array<string, scalar> $values A map of field keys to values.
     * @psalm-param array<FieldName, FieldValue>
     * @param Request $request
     * @return array A map of WP_comment properties to values.
     */
    protected function createCommentData(array $values, Request $request): array
    {
        $source = $this->relationshipContext->sourceComment();
        $hasRemote = $this->relationshipContext->hasRemoteComment();
        $isCopyContentSelected = $values['remote-content-copy'] ?? false;

        $comment = [];

        if (!$hasRemote) {
            $comment['comment_post_ID'] = $this->relationshipContext->remotePostId();
            $comment['comment_agent'] = $source->comment_agent;
            $comment['comment_author_IP'] = $source->comment_author_IP;
            $comment['comment_parent'] = $this->relationshipContext->remoteCommentParentId();
            $comment['comment_type'] = $source->comment_type;
            $comment['comment_meta'] = $source->comment_meta;
            $comment['comment_content'] = $source->comment_content;
            $comment['user_id'] = $source->user_id;
        }

        if ($hasRemote) {
            $comment['comment_ID'] = $this->relationshipContext->remoteCommentId();
        }

        $wpCommentFieldNameToFieldNameMap = [
            'comment_approved' => 'remote-status',
            'comment_author' => 'remote-author-name',
            'comment_author_email' => 'remote-author-email',
            'comment_author_url' => 'remote-author-url',
        ];

        foreach ($wpCommentFieldNameToFieldNameMap as $wpCommentFieldName => $fieldName) {
            if (!$isCopyContentSelected && !$this->isFieldValueChanged($fieldName, $request) && $hasRemote) {
                continue;
            }

            $sourceValue = $source->{$wpCommentFieldName};
            $defaultValue = $hasRemote ? '' :  $sourceValue;
            $fieldValue = $values[$fieldName] ?? $defaultValue;
            $comment[$wpCommentFieldName] = $isCopyContentSelected ? $sourceValue : $fieldValue;
        }

        return $comment;
    }

    /**
     * Saves the given comment (inserts or updates) for given relation type.
     *
     * @param array $comment A map of WP_comment properties to values.
     * @param string $relationType The relation type (existing, new, remove, leave).
     * @return int The inserted or updated comment ID.
     */
    protected function saveComment(array $comment, string $relationType): int
    {
        $relationType === 'new'
            ? $commentId = wp_insert_comment(wp_slash($comment))
            : wp_update_comment(wp_slash($comment));

        $commentId = $commentId ?? $comment['comment_ID'] ?? 0;

        if (!is_numeric($commentId) || !$commentId) {
            throw new RuntimeException(__(
                'Error updating translation: error updating comment in database.',
                'multilingualpress'
            ));
        }

        return $commentId;
    }

    /**
     * Checks if the field value with given name is changed for given request.
     *
     * @param string $fieldName The field name.
     * @param Request $request
     * @return bool true if the field value with given name is changed, otherwise false.
     */
    protected function isFieldValueChanged(string $fieldName, Request $request): bool
    {
        $changedFields = $this->fieldsHelper->fieldRequestValue($request, 'changed_fields') ?? '';
        $changedFieldNames = explode(',', $changedFields);

        return in_array($fieldName, $changedFieldNames, true);
    }
}
