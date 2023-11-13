<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\Comments\TranslationUi\Field;

use Inpsyde\MultilingualPress\Module\Comments\RelationshipContext\CommentsRelationshipContextInterface;
use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelper;
use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelperFactoryInterface;
use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelperInterface;
use RuntimeException;

class CommentMetaboxCopyContent implements CommentMetaboxField
{
    public const FILTER_COPY_CONTENT_IS_CHECKED = 'multilingualpress.Comments.copy_content_is_checked';

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
     * @inheritDoc
     */
    public function render(CommentsRelationshipContextInterface $relationshipContext): void
    {
        $helper = $this->createHelper($relationshipContext->remoteSiteId());
        $id = $helper->fieldId($this->key());
        $name = $helper->fieldName($this->key());

        /**
         * Filter if the input should be prechecked.
         *
         * @param bool $checked
         */
        $checked = (bool)apply_filters(
            self::FILTER_COPY_CONTENT_IS_CHECKED,
            false
        );
        ?>
        <tr>
            <th scope="row">
                <label for="<?= esc_attr($id) ?>">
                    <?= esc_html($this->label()) ?>
                </label>
            </th>
            <td>
                <input
                    type="checkbox"
                    name="<?= esc_attr($name) ?>"
                    value="1"
                    id="<?= esc_attr($id) ?>"
                    <?php checked($checked) ?>
                />
                <?php
                esc_html_e(
                    'Overwrites all the translated author field values with the ones from source.',
                    'multilingualpress'
                );
                ?>
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
