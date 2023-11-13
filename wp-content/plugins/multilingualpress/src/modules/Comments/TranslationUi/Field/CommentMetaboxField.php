<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\Comments\TranslationUi\Field;

use Inpsyde\MultilingualPress\Module\Comments\RelationshipContext\CommentsRelationshipContextInterface;

/**
 * Represents the comment metabox field.
 */
interface CommentMetaboxField
{
    /**
     * The key of the field.
     *
     * @return string
     */
    public function key(): string;

    /**
     * The field label.
     *
     * @return string
     */
    public function label(): string;

    /**
     * Renders the field by given context.
     *
     * @param CommentsRelationshipContextInterface $relationshipContext
     */
    public function render(CommentsRelationshipContextInterface $relationshipContext): void;
}
