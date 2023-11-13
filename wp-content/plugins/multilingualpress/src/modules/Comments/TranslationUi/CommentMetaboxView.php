<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\Comments\TranslationUi;

use Inpsyde\MultilingualPress\Framework\Admin\Metabox;
use Inpsyde\MultilingualPress\Module\Comments\RelationshipContext\CommentsRelationshipContextInterface;
use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelperInterface;

class CommentMetaboxView implements Metabox\View
{
    /**
     * @var CommentMetaboxTabInterface[]
     */
    protected $metaboxTabs;

    /**
     * @var MetaboxFieldsHelperInterface
     */
    protected $helper;

    /**
     * @var CommentsRelationshipContextInterface
     */
    protected $relationshipContext;

    public function __construct(
        array $metaboxTabs,
        MetaboxFieldsHelperInterface $helper,
        CommentsRelationshipContextInterface $relationshipContext
    ) {

        $this->metaboxTabs = $metaboxTabs;
        $this->helper = $helper;
        $this->relationshipContext = $relationshipContext;
    }

    /**
     * @inheritdoc
     */
    public function render(Metabox\Info $info)
    {
        // phpcs:enable
        $remotePostIsTrashed = $this->relationshipContext->hasRemoteComment()
            && $this->relationshipContext->remoteComment()->comment_approved === 'trash';

        if ($remotePostIsTrashed) {
            $this->metaboxTabs = array_filter(
                $this->metaboxTabs,
                static function (CommentMetaboxTabInterface $tab): bool {
                    return $tab->id() === 'tab-relation';
                }
            );
            $this->renderTrashedMessage();
        }

        $changedFieldsName = $this->helper->fieldName('changed_fields');

        ?>
        <div class="mlp-translation-metabox mlp-translation-metabox--post" <?php $this->boxDataAttributes() ?>>
            <?php $this->relationshipContext->renderFields($this->helper) ?>
            <input type="hidden" class="changed-fields" name="<?= esc_attr($changedFieldsName) ?>" value="">

            <ul class="nav-tab-wrapper wp-clearfix">
                <?php
                foreach ($this->metaboxTabs as $tab) {
                    if (!$tab->enabled($this->relationshipContext)) {
                        continue;
                    }
                    $this->renderTabAnchor($tab);
                }
                ?>
            </ul>
            <?php
            foreach ($this->metaboxTabs as $tab) {
                $enabled = $tab->enabled($this->relationshipContext);
                $tabId = $tab->id();
                do_action(CommentMetabox::ID_PREFIX . "before_tab_{$tabId}", $enabled);
                $enabled and $tab->render($this->helper, $this->relationshipContext);
                do_action(CommentMetabox::ID_PREFIX . "after_tab_{$tabId}", $enabled);
            }
            ?>
        </div>
        <?php
    }

    /**
     * Renders the metabox wrapper div HTML attributes.
     *
     * @return void
     */
    protected function boxDataAttributes(): void
    {
        $remoteCommentId = $this->relationshipContext->remoteCommentId();
        $remoteCommentEditLink = $this->relationshipContext->hasRemoteComment() ? get_edit_comment_link($remoteCommentId) : '';
        ?>
        data-source-site="<?= esc_attr((string)$this->relationshipContext->sourceSiteId()) ?>"
        data-source-comment="<?= esc_attr((string)$this->relationshipContext->sourceCommentId()) ?>"
        data-remote-site="<?= esc_attr((string)$this->relationshipContext->remoteSiteId()) ?>"
        data-remote-comment="<?= esc_attr((string)$remoteCommentId) ?>"
        data-remote-link="<?= esc_attr($remoteCommentEditLink) ?>"
        data-remote-link-label="<?= esc_attr__('Edit', 'multilingualpress') ?>"
        data-remote-post="<?= esc_attr((string)$this->relationshipContext->remotePostId()) ?>"
        <?php
    }

    /**
     * Renders the metabox tab anchors.
     *
     * @param CommentMetaboxTabInterface $tab
     */
    protected function renderTabAnchor(CommentMetaboxTabInterface $tab): void
    {
        $tabId = $tab->id();
        $label = (string)apply_filters(
            "multilingualpress.translation_post_metabox_tab_{$tabId}_anchor",
            $tab->label()
        );

        printf(
            '<li class="nav-tab" id="tab-anchor-%1$s"><a href="#%1$s">%2$s</a></li>',
            esc_attr($this->helper->fieldId($tabId)),
            esc_html($label)
        );
    }

    /**
     * Renders the "trashed" message.
     */
    private function renderTrashedMessage(): void
    {
        ?>
        <div class="mlp-warning">
            <p>
                <?php
                esc_html_e(
                    'The currently connected translation post is trashed.',
                    'multilingualpress'
                );
                print ' ';
                esc_html_e(
                    'Edit the relationship or no further editing will be possible.',
                    'multilingualpress'
                );
                ?>
            </p>
        </div>
        <?php
    }
}
