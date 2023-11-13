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

class CommentMetaboxTab implements CommentMetaboxTabInterface
{
    public const ACTION_AFTER_TRANSLATION_UI_TAB = 'multilingualpress.TranslationUi.Comment.AfterTranslationUiTab';
    public const ACTION_BEFORE_TRANSLATION_UI_TAB = 'multilingualpress.TranslationUi.Comment.BeforeTranslationUiTab';
    public const FILTER_TRANSLATION_UI_SHOW_TAB = 'multilingualpress.TranslationUi.Comment.TranslationUiShowTab';
    public const FILTER_COMMENT_METABOX_TAB = 'multilingualpress.TranslationUi.Comment.TranslationUiTab';

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var CommentMetaboxField[]
     */
    protected $fields;

    public function __construct(string $id, string $label, array $fields)
    {
        $this->id = $id;
        $this->label = $label;
        $this->fields = $fields;
    }

    /**
     * @inheritDoc
     */
    public function id(): string
    {
        return $this->id;
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
    public function fields(): array
    {
        return $this->fields;
    }

    /**
     * @inheritDoc
     */
    public function enabled(CommentsRelationshipContextInterface $relationshipContext): bool
    {
        if (!$this->fields) {
            return false;
        }

        $enabled = (bool)apply_filters(
            self::FILTER_TRANSLATION_UI_SHOW_TAB,
            true,
            $this->id,
            $relationshipContext
        );

        return (bool)apply_filters(
            self::FILTER_TRANSLATION_UI_SHOW_TAB . "_{$this->id}",
            $enabled,
            $relationshipContext
        );
    }

    /**
     * @inheritDoc
     */
    public function render(MetaboxFieldsHelper $helper, CommentsRelationshipContextInterface $relationshipContext)
    {
        if (!$this->enabled($relationshipContext)) {
            return;
        }

        $id = $this->id();
        ?>
        <div class="wp-tab-panel"
             id="<?= esc_attr($helper->fieldId($this->id())) ?>"
             data-tab-id="<?= esc_attr($this->id()) ?>"
        >
            <table class="form-table <?= sanitize_html_class($this->id()) ?>">
                <tbody>
                <?php
                do_action(self::ACTION_BEFORE_TRANSLATION_UI_TAB . "_{$id}_fields");
                $filterName = self::FILTER_COMMENT_METABOX_TAB . "_{$id}_fields";
                $tabFields = apply_filters($filterName, $this->fields(), $relationshipContext);
                foreach ($tabFields as $field) {
                    $field->render($relationshipContext);
                }
                do_action(self::ACTION_AFTER_TRANSLATION_UI_TAB . "_{$id}_fields", $relationshipContext);
                ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
