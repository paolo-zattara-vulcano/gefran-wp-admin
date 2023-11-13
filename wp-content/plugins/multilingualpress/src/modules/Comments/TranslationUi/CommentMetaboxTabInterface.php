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

namespace Inpsyde\MultilingualPress\Module\Comments\TranslationUi;

use Inpsyde\MultilingualPress\Module\Comments\RelationshipContext\CommentsRelationshipContextInterface;
use Inpsyde\MultilingualPress\Module\Comments\TranslationUi\Field\CommentMetaboxField;
use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelper;

/**
 * Represents the comment metabox tab.
 */
interface CommentMetaboxTabInterface
{
    /**
     * The id of the metabox tab.
     *
     * @return string
     */
    public function id(): string;

    /**
     * The label to show to the tab header.
     *
     * @return string
     */
    public function label(): string;

    /**
     * The fields collection for the current tab.
     *
     * @return CommentMetaboxField[]
     */
    public function fields(): array;

    /**
     * If the metabox tab is enabled or not.
     *
     * @param CommentsRelationshipContextInterface $relationshipContext
     * @return bool
     */
    public function enabled(CommentsRelationshipContextInterface $relationshipContext): bool;

    /**
     * Render the metabox markup.
     *
     * @param MetaboxFieldsHelper $helper
     * @param CommentsRelationshipContextInterface $relationshipContext
     */
    public function render(MetaboxFieldsHelper $helper, CommentsRelationshipContextInterface $relationshipContext);
}
