<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\Comments\TranslationUi\Field;

use Inpsyde\MultilingualPress\Module\Comments\RelationshipContext\CommentsRelationshipContextInterface;
use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelper;
use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelperFactoryInterface;
use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelperInterface;
use RuntimeException;

class CommentMetaboxStatus implements CommentMetaboxField
{
    public const FILTER_TRANSLATION_UI_POST_STATUSES = 'multilingualpress.translation_ui_comment_statuses';

    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var MetaboxFieldsHelperFactoryInterface
     */
    protected $helperFactory;

    public function __construct(string $key, string $label, MetaboxFieldsHelperFactoryInterface $helperFactory)
    {
        $this->key = $key;
        $this->label = $label;
        $this->helperFactory = $helperFactory;
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
        return $this->label;
    }

    /**
     * Available comment statuses.
     *
     * @return array<string> The list of available comment statuses
     */
    protected function availableStatuses(): array
    {
        return (array)apply_filters(
            self::FILTER_TRANSLATION_UI_POST_STATUSES,
            [
                '1' => __('Approved', 'multilingualpress'),
                '0' => __('Pending', 'multilingualpress'),
                'spam' => __('Spam', 'multilingualpress'),
                'trash' => __('Trash', 'multilingualpress'),
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function render(CommentsRelationshipContextInterface $relationshipContext): void
    {
        $helper = $this->createHelper($relationshipContext->remoteSiteId());
        $id = $helper->fieldId($this->key);
        $name = $helper->fieldName($this->key);
        $hasRemoteComment = $relationshipContext->hasRemoteComment();
        $current = $hasRemoteComment ? wp_get_comment_status($relationshipContext->remoteCommentId()) : '0';
        $current = $current === 'unapproved' ? '0' : $current;
        ?>
        <tr>
            <th scope="row">
                <label for="<?= esc_attr($id) ?>">
                    <?= esc_html($this->label()) ?>
                </label>
            </th>
            <td>
                <select id="<?= esc_attr($id) ?>" name="<?= esc_attr($name) ?>">
                    <?php foreach ($this->availableStatuses() as $value => $label) : ?>
                        <option
                            value="<?= esc_attr($value) ?>"
                            <?= selected($value, $current) ?>>
                            <?= esc_html($label) ?>
                        </option>
                    <?php endforeach ?>
                </select>
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
