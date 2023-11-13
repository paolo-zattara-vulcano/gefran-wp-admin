<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\WooCommerce\TranslationUi\Review\Field;

use Inpsyde\MultilingualPress\Module\Comments\RelationshipContext\CommentsRelationshipContextInterface;
use Inpsyde\MultilingualPress\Module\Comments\TranslationUi\Field\CommentMetaboxField;
use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelper;
use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelperFactoryInterface;
use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelperInterface;
use RuntimeException;

class CommentMetaboxReviewRating implements CommentMetaboxField
{
    /**
     * @var string
     */
    private $key;

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
    public function label(bool $hasRemoteComment = false): string
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
        $value = $hasRemoteComment
            ? $this->reviewRatingMetaValue($relationshipContext->remoteSiteId(), $relationshipContext->remoteCommentId())
            : $this->reviewRatingMetaValue($relationshipContext->sourceSiteId(), $relationshipContext->sourceCommentId());
        ?>
        <tr>
            <th scope="row">
                <label for="<?= esc_attr($id) ?>">
                    <?= esc_html($this->label($hasRemoteComment)) ?>
                </label>
            </th>
            <td>
                <select
                    name="<?= esc_attr($name) ?>"
                    id="<?= esc_attr($id) ?>">
                    <?php for ($rating = 0; $rating <= 5; $rating++) :?>
                        <option
                            value="<?= esc_attr($rating) ?>"
                            <?= selected($rating, $value) ?>>
                            <?= esc_html($rating ?: 'none') ?>
                        </option>
                    <?php endfor;?>
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

    /**
     * Get the rating meta value of a given review from a given site.
     *
     * @param int $siteId The site ID.
     * @param int $commentId The review ID.
     * @return int The review rating meta value.
     */
    protected function reviewRatingMetaValue(int $siteId, int $commentId): int
    {
        switch_to_blog($siteId);
        $reviewRatingMetaValue = get_comment_meta($commentId, 'rating', true);
        restore_current_blog();

        return (int)$reviewRatingMetaValue ?: 0;
    }
}
