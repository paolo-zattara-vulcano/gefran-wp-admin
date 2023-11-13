<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\Comments\TranslationUi\Field;

use Inpsyde\MultilingualPress\Module\Comments\RelationshipContext\CommentsRelationshipContextInterface;
use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelper;
use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelperFactoryInterface;
use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelperInterface;
use RuntimeException;

class CommentMetaboxFieldAuthorEmail implements CommentMetaboxField
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var MetaboxFieldsHelperFactoryInterface
     */
    protected $helperFactory;

    /**
     * @var string
     */
    protected $label;

    public function __construct(string $key, string $label, MetaboxFieldsHelperFactoryInterface $helperFactory)
    {
        $this->key = $key;
        $this->helperFactory = $helperFactory;
        $this->label = $label;
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
        $hasRemoteComment = $relationshipContext->hasRemoteComment();
        $value = $hasRemoteComment ? $relationshipContext->remoteComment()->comment_author_email : '';
        ?>
        <tr>
            <th scope="row">
                <label for="<?= esc_attr($id) ?>">
                    <?= esc_html($this->label()) ?>
                </label>
            </th>
            <td>
                <input
                    type="text"
                    name="<?= esc_attr($name) ?>"
                    id="<?= esc_attr($id) ?>"
                    class="large-text"
                    value="<?= esc_attr($value) ?>">
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
