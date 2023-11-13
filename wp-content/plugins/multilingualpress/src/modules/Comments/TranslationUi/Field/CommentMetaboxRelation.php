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

namespace Inpsyde\MultilingualPress\Module\Comments\TranslationUi\Field;

use Inpsyde\MultilingualPress\Module\Comments\RelationshipContext\CommentsRelationshipContextInterface;
use Inpsyde\MultilingualPress\Module\Comments\TranslationUi\Ajax\AjaxSearchCommentRequestHandler;
use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelper;
use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelperFactoryInterface;
use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelperInterface;
use RuntimeException;

use function Inpsyde\MultilingualPress\siteLocaleName;

class CommentMetaboxRelation implements CommentMetaboxField
{
    /**
     * @var MetaboxFieldsHelperFactoryInterface
     */
    protected $helperFactory;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $label;

    public function __construct(string $key, MetaboxFieldsHelperFactoryInterface $helperFactory)
    {
        $this->helperFactory = $helperFactory;
        $this->key = $key;
    }

    /**
     * @inheritDoc
     */
    public function key(): string
    {
        return $this->key;
    }

    /**
     * @inheritDoc
     */
    public function label(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
     */
    public function render(CommentsRelationshipContextInterface $relationshipContext): void
    {
        // phpcs:enable

        $helper = $this->createHelper($relationshipContext->remoteSiteId());
        $language = siteLocaleName($relationshipContext->remoteSiteId());

        $hasRemoteComment = $relationshipContext->hasRemoteComment();
        $currently = __('Currently not connected.', 'multilingualpress');
        $currentlyMarkupFormat = '<strong>%s</strong>';
        if ($hasRemoteComment) {
            // translators: %1$s is the comment edit link
            $format = __('Currently connected with Comment: %1$s', 'multilingualpress');
            $comment = $relationshipContext->remoteComment();

            $editCommentId = $relationshipContext->remoteCommentId();
            $editCommentLink = get_edit_comment_link($editCommentId);
            $editCommentLinkMarkup = '';

            if (wp_http_validate_url($editCommentLink)) {
                // translators: 1 is remote comment edit link, 2 is the comment id, 3 the comment author, 4 the comment author email
                $editCommentLinkMarkupFormat =
                    '<a class="mlp-entity-edit-link" href="%1$s">Id: %2$s, Author: %3$s - %4$s</a>';
                $editCommentLinkMarkup = sprintf(
                    $editCommentLinkMarkupFormat,
                    esc_url($editCommentLink),
                    $editCommentId,
                    esc_html($comment->comment_author) ?? '',
                    esc_html($comment->comment_author_email) ?? ''
                );
            }

            $currently = sprintf($format, $editCommentLinkMarkup ?: $editCommentId);

            $currentlyMarkupFormat = '<div class="currently-connected">%s</div>';
        }

        ?>
        <tr class="main-row">
            <td>
                <?= sprintf($currentlyMarkupFormat, wp_kses_post($currently)) ?>
                <?php
                $name = $helper->fieldName($this->key());
                $commentType = $relationshipContext->sourceComment()->comment_type;

                $leaveType = $hasRemoteComment ? 'leave' : 'nothing';
                $this->relationFieldMarkup(
                    $this->relationFieldId($leaveType, $helper),
                    $name,
                    $leaveType,
                    $this->relationFieldDescription($leaveType, $language, $hasRemoteComment, $commentType)
                );

                $type = $hasRemoteComment ? 'remove' : 'new';
                $this->relationFieldMarkup(
                    $this->relationFieldId($type, $helper),
                    $name,
                    $type,
                    $this->relationFieldDescription($type, $language, $hasRemoteComment, $commentType)
                );

                $this->relationFieldMarkup(
                    $this->relationFieldId('existing', $helper),
                    $name,
                    'existing',
                    $this->relationFieldDescription('existing', $language, $hasRemoteComment, $commentType)
                );
                ?>
            </td>
        </tr>
        <?php
        $this->searchRow($helper);
        $this->buttonRow();
    }

    /**
     * Creates a value for 'id' HTML attribute based on relation type.
     *
     * @param string $type The relation type (existing, new, remove, leave).
     * @return string The value for 'id' HTML attribute.
     */
    protected function relationFieldId(string $type, MetaboxFieldsHelperInterface $helper): string
    {
        return $helper->fieldId("{$this->key()}-{$type}");
    }

    /**
     * The relation field markup based on the relation type.
     *
     * @param string $fieldId The value for 'id' HTML attribute.
     * @param string $fieldName The value for 'name' HTML attribute.
     * @param string $type The relation type (existing, new, remove, leave).
     * @param string $description The field description.
     * @return void
     */
    protected function relationFieldMarkup(
        string $fieldId,
        string $fieldName,
        string $type,
        string $description
    ): void {

        ?>
        <p>
            <label for="<?= esc_attr($fieldId) ?>">
                <input
                    type="radio"
                    id="<?= esc_attr($fieldId) ?>"
                    value="<?= esc_attr($type) ?>"
                    name="<?= esc_attr($fieldName) ?>"
                    <?php if ($type === 'leave') :?>
                        checked
                    <?php endif;?>
                >
                <?= esc_html($description)?>
            </label>
        </p>
        <?php
    }

    /**
     * Returns the relation field description based on relation type.
     *
     * @param string $type The relation type (existing, new, remove, leave).
     * @param string $languageName The language name.
     * @param bool $hasRemoteComment True if remote connection exists, otherwise false.
     * @param string $commentType The comment type. can be 'comment' or 'review' or custom type.
     * @return string The relation field description
     */
    protected function relationFieldDescription(string $type, string $languageName, bool $hasRemoteComment, string $commentType): string
    {
        switch ($type) {
            case 'new':
                return sprintf(
                // translators: %1$s is the comment type and %2$s is the language name.
                    __('Create a new %1$s, and use it as translation in %2$s.', 'multilingualpress'),
                    $commentType,
                    $languageName
                );
            case 'existing':
                return sprintf(
                // translators: %1$s is the comment type and %2$s is the language name.
                    __('Select an existing %1$s to be used as translation in %2$s.', 'multilingualpress'),
                    $commentType,
                    $languageName
                );
            case 'remove':
                return sprintf(
                // translators: %s is the language name.
                    __('Remove connection (don\'t translate in %s).', 'multilingualpress'),
                    $languageName
                );
            default:
                return $hasRemoteComment
                    ? sprintf(
                    // translators: %1$s is the comment type.
                        __('Do not change connected %1$s..', 'multilingualpress'),
                        $commentType
                    )
                    : __('Keep not connected.', 'multilingualpress');
        }
    }

    /**
     * The "Search for remote site comments to connect" input markup.
     *
     * @param MetaboxFieldsHelper $helper
     * @return void
     */
    protected function searchRow(MetaboxFieldsHelper $helper): void
    {
        $name = $helper->fieldName('search_comment_id');
        $inputId = $helper->fieldId('search_comment_id');
        $resultsId = $helper->fieldId('search-results');
        $placeholder = __('Start typing to search...', 'multilingualpress');
        ?>
        <tr class="search-input-row" style="display: none">
            <td>
                <input
                    id="<?= esc_attr($inputId) ?>"
                    type="text"
                    class="regular-text"
                    data-results="#<?= esc_attr($resultsId) ?>"
                    data-action="<?= esc_attr(AjaxSearchCommentRequestHandler::ACTION) ?>"
                    placeholder="<?= esc_attr($placeholder) ?>"
                    aria-label="<?= esc_attr__('Search', 'multilingualpress') ?>">
            </td>
        </tr>
        <tr>
            <td id="<?= esc_attr($resultsId) ?>" class="search-results" style="display: none">
                <table class="widefat striped">
                    <tbody>
                    <tr class="search-results-row" style="display: none">
                        <td>
                            <label>
                                <input
                                    type="radio"
                                    name="<?= esc_attr($name) ?>"
                                    value="0"
                                    aria-label="">
                                <span></span>
                            </label>
                        </td>
                    </tr>
                    <tr class="search-results-none" style="display: none">
                        <td>
                            <?php
                            esc_html_e(
                                'No comments found matching search.',
                                'multilingualpress'
                            );
                            ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        <?php
    }

    /**
     * The update relation button markup.
     *
     * @return void
     */
    protected function buttonRow(): void
    {
        ?>
        <tr>
            <td>
                <button
                    style="display:none;"
                    class="button-primary update-relationship">
                    <?php esc_html_e('Update now', 'multilingualpress') ?>
                </button>
            </td>
        </tr>
        <?php
    }

    /**
     * Creates a new metabox fields helper.
     *
     * @param int $siteId The ID of the site for which to create a helper.
     * @return MetaboxFieldsHelperInterface The new helper.
     * @throws RuntimeException If problem creating.
     */
    protected function createHelper(int $siteId): MetaboxFieldsHelper
    {
        return $this->helperFactory->createMetaboxFieldsHelper($siteId);
    }
}
