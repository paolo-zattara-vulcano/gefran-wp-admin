<?php

declare(strict_types=1);

# -*- coding: utf-8 -*-
/*
 * This file is part of the MultilingualPress package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\MultilingualPress\Framework\Admin\Metabox;

use Inpsyde\MultilingualPress\Core\PostTypeRepository;
use Inpsyde\MultilingualPress\Framework\Admin\AdminNotice;
use Inpsyde\MultilingualPress\Framework\Admin\PersistentAdminNotices;
use Inpsyde\MultilingualPress\Framework\Auth\AuthFactoryException;
use Inpsyde\MultilingualPress\Framework\Entity;
use Inpsyde\MultilingualPress\Framework\Http\RequestGlobalsManipulator;
use Inpsyde\MultilingualPress\Framework\Module\ModuleManager;
use Inpsyde\MultilingualPress\Framework\Nonce\WpNonce;
use Inpsyde\MultilingualPress\Module\Comments\ServiceProvider as CommentsModule;
use WP_Comment;
use WP_Post;
use WP_Term;

use function Inpsyde\MultilingualPress\printNonceField;
use function Inpsyde\MultilingualPress\siteExists;
use function Inpsyde\MultilingualPress\wpHookProxy;

class Metaboxes
{
    const REGISTER_METABOXES = 'multilingualpress.register_metaboxes';
    const ACTION_INSIDE_METABOX_AFTER = 'multilingualpress.inside_box_after';
    const ACTION_INSIDE_METABOX_BEFORE = 'multilingualpress.inside_box_before';
    const ACTION_SHOW_METABOXES = 'multilingualpress.show_metaboxes';
    const ACTION_SHOWED_METABOXES = 'multilingualpress.showed_metaboxes';
    const ACTION_SAVE_METABOXES = 'multilingualpress.save_metaboxes';
    const ACTION_SAVED_METABOXES = 'multilingualpress.saved_metaboxes';
    const FILTER_SAVE_METABOX_ON_EMPTY_POST = 'multilingualpress.metabox_save_on_empty_post';
    const FILTER_METABOX_ENABLED = 'multilingualpress.metabox_enabled';
    protected const FILTER_TAXONOMY_METABOXES_ORDER = 'multilingualpress.taxonomy_metaboxes_order';

    /**
     * @var RequestGlobalsManipulator
     */
    private $globalsManipulator;

    /**
     * @var PersistentAdminNotices
     */
    private $notices;

    /**
     * @var Metabox[]
     */
    private $boxes = [];

    /**
     * @var bool
     */
    private $locked = true;

    /**
     * @var Entity
     */
    private $entity;

    /**
     * @var string
     */
    private $registeringFor = '';

    /**
     * @var string
     */
    private $saving = '';

    /**
     * @var MetaboxUpdater
     */
    private $metaboxUpdater;

    /**
     * @var PostTypeRepository
     */
    protected $postTypeRepository;

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    public function __construct(
        RequestGlobalsManipulator $globalsManipulator,
        PersistentAdminNotices $notices,
        MetaboxUpdater $metaboxUpdater,
        PostTypeRepository $postTypeRepository,
        ModuleManager $moduleManager
    ) {

        $this->globalsManipulator = $globalsManipulator;
        $this->notices = $notices;
        $this->metaboxUpdater = $metaboxUpdater;
        $this->postTypeRepository = $postTypeRepository;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @return void
     */
    public function init()
    {
        if (!is_admin()) {
            return;
        }

        add_action('current_screen', function (\WP_Screen $screen) {
            if ($screen->taxonomy ?? false) {
                $this->initForTerm($screen->taxonomy);
                $filterPriority = (int) apply_filters(self::FILTER_TAXONOMY_METABOXES_ORDER, 10);
                add_action("{$screen->taxonomy}_edit_form", [$this, 'printTermBoxes'], $filterPriority);
                return;
            }
            if (!empty($screen->post_type) && in_array($screen->post_type, $this->postTypeRepository->supportedPostTypes(), true)) {
                $this->initForPost();
            }

            if ($this->moduleManager->isModuleActive(CommentsModule::MODULE_ID) && $screen->base === 'comment') {
                $this->initForComment();
            }
        }, PHP_INT_MAX);
    }

    /**
     * @param Metabox[] $boxes
     *
     * @return Metaboxes
     */
    public function addBox(Metabox ...$boxes): Metaboxes
    {
        if (!is_admin()) {
            return $this;
        }

        if ($this->locked) {
            throw new \BadMethodCallException('Cannot add boxes when controller is locked.');
        }

        if (
            !$this->entity->isValid()
            || !in_array($this->registeringFor, [Metabox::SAVE, Metabox::SHOW], true)
        ) {
            return $this;
        }

        foreach ($boxes as $box) {
            $this->boxes[$box->createInfo($this->registeringFor, $this->entity)->id()] = $box;
        }

        return $this;
    }

    /**
     * WordPress does not print metaboxes for terms, let's fix this.
     *
     * @param WP_Term $term
     */
    public function printTermBoxes(WP_Term $term)
    {
        if (!is_admin() || current_filter() !== "{$term->taxonomy}_edit_form") {
            return;
        }

        global $wp_meta_boxes;
        if (empty($wp_meta_boxes["edit-{$term->taxonomy}"])) {
            return;
        }

        $script = '!function(J,D){J(function(){'
            . 'J(".termbox-container .hndle").removeClass("hndle");'
            . 'J(D).on("click",".termbox-container button.handlediv",function(){'
            . 'var D=J(this),t=D.siblings(".inside");t.toggle();'
            . 'var e=t.is(":visible")?"true":"false";'
            . 'D.attr("aria-expanded",e)})})}(jQuery,document);';
        wp_enqueue_script('jquery-ui-sortable');
        wp_add_inline_script('jquery-ui-sortable', $script);

        print '<div id="poststuff"><div class="termbox-container">';
        // WordPress does not print metaboxes for terms, let's fix this
        do_meta_boxes("edit-{$term->taxonomy}", 'side', $term);
        do_meta_boxes("edit-{$term->taxonomy}", 'normal', $term);
        do_meta_boxes("edit-{$term->taxonomy}", 'advanced', $term);
        print '</div></div>';
    }

    /**
     * @return bool
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
     */
    private function initForPost(): bool
    {
        // phpcs:enable

        // Show Boxes
        add_action(
            'add_meta_boxes',
            function ($postType, $post) {
                if ($post instanceof WP_Post) {
                    $entity = new Entity($post);
                    $this->addMetaBoxes($entity);
                }
            },
            100,
            2
        );

        // Save Boxes even if WordPress says content is empty.
        add_filter(
            'wp_insert_post_empty_content',
            wpHookProxy(
                function (bool $empty, array $data): bool {
                    global $post;
                    if (!$empty || !$post instanceof WP_Post || !$post->ID) {
                        return $empty;
                    }

                    $allowOnEmptyPost = apply_filters(
                        self::FILTER_SAVE_METABOX_ON_EMPTY_POST,
                        true,
                        $post,
                        $data
                    );

                    if ($allowOnEmptyPost) {
                        $this->onPostSave($post);
                    }

                    return $empty;
                }
            ),
            PHP_INT_MAX,
            2
        );

        // Save Boxes
        add_action(
            'wp_insert_post',
            function ($postId, WP_Post $post) {
                if ($post->post_status === 'trash') {
                    return;
                }
                $this->onPostSave($post);
            },
            100,
            2
        );

        return true;
    }

    /**
     * @return void
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
     */
    private function initForComment(): void
    {
        // phpcs:enable

        // Show Boxes
        add_action(
            'add_meta_boxes',
            function (string $name, WP_Comment $comment) {
                $entity = new Entity($comment);
                $this->addMetaBoxes($entity);
            },
            100,
            2
        );

        // Save Boxes
        add_action(
            'edit_comment',
            function (int $commentId) {
                $comment = get_comment($commentId);
                if (!$comment || is_wp_error($comment)) {
                    return;
                }

                $this->onCommentSave($comment);
            },
            100,
            2
        );
    }

    /**
     * @param string $taxonomy
     *
     * @return bool
     */
    private function initForTerm(string $taxonomy): bool
    {
        // Show Boxes
        add_action(
            "{$taxonomy}_pre_edit_form",
            function (WP_Term $term) {
                $entity = new Entity($term);
                $this->addMetaBoxes($entity);
            },
            1
        );

        // Save Boxes
        add_action(
            'edit_term',
            wpHookProxy(
                function (int $termId, int $termTaxonomyId, string $termTaxonomy) use ($taxonomy) {
                    // This check allows to edit term object inside BoxAction::save() without recursion.
                    if ($this->saving === 'WP_Term') {
                        return;
                    }

                    $term = get_term_by('term_taxonomy_id', $termTaxonomyId);

                    if (
                        !$term instanceof WP_Term
                        || (int)$term->term_id !== $termId
                        || $term->taxonomy !== $termTaxonomy
                        || $term->taxonomy !== $taxonomy
                    ) {
                        return;
                    }

                    $entity = new Entity($term);
                    $this->saveMetaboxesActions($entity);
                }
            ),
            100,
            3
        );

        return true;
    }

    /**
     * @param Entity $entity
     * @param string $showOrSave
     */
    private function prepareTarget(Entity $entity, string $showOrSave)
    {
        $this->entity = $entity;
        $this->registeringFor = $showOrSave;
        $this->boxes = [];
        $this->locked = false;
        if ($this->entity->isValid()) {
            do_action(self::REGISTER_METABOXES, $this, $this->entity, $showOrSave);
        }
        $this->locked = true;
    }

    /**
     * @param Metabox $box
     * @return bool
     */
    private function isBoxEnabled(Metabox $box): bool
    {
        if (!$this->entity->isValid()) {
            return false;
        }

        $object = $this->entity->expose();
        $accept = $box->isValid($this->entity);

        return (bool)apply_filters(self::FILTER_METABOX_ENABLED, $accept, $box, $object);
    }

    /**
     * @param Metabox $box
     * @param string $boxId
     */
    private function addMetabox(Metabox $box, string $boxId)
    {
        if (!$this->isBoxEnabled($box)) {
            return;
        }

        $entity = $this->entity;

        $isPost = $entity->is(WP_Post::class);
        $isComment = $entity->is(WP_Comment::class);
        /** @var WP_Post|WP_Term|WP_Comment $object */
        $object = $entity->expose();
        $info = $box->createInfo(Metabox::SHOW, $entity);

        $type = strtolower(str_replace("WP_", '', $this->entity->type()));
        $boxSuffix = "-{$type}box";

        $context = $info->context();
        $screen = $isPost ? null : ($isComment ? 'comment' : "edit-{$object->taxonomy}");
        ($context === Info::CONTEXT_SIDE && $isPost) and $screen = $object->post_type;

        add_meta_box(
            $boxId . $boxSuffix,
            $info->title(),
            static function ($object) use ($boxId, $box, $info, $entity) { // phpcs:ignore
                $siteId = $box->siteId();
                if (!siteExists($siteId)) {
                    // translators: %s is the site ID.
                    $message = __('Site %s is not accessible.', 'multilingualpress');
                    print esc_html(sprintf($message, $siteId));
                    return;
                }

                $objectId = $entity->id();

                printNonceField((new WpNonce($boxId . "-{$objectId}"))->withSite($siteId));
                switch_to_blog($siteId);
                do_action(self::ACTION_INSIDE_METABOX_BEFORE, $box, $object, $info);
                $view = $box->view($entity);
                $view->render($info);
                do_action(self::ACTION_INSIDE_METABOX_AFTER, $box, $object, $info);
                restore_current_blog();
            },
            $screen,
            $context,
            $info->priority()
        );
    }

    /**
     * @param WP_Post $post
     */
    private function onPostSave(WP_Post $post)
    {
        // This check allows to edit post object inside BoxAction::save() without recursion.
        if ($this->saving === 'WP_Post') {
            return;
        }

        if (wp_is_post_autosave($post) || wp_is_post_revision($post)) {
            return;
        }

        $entity = new Entity($post);
        $this->saveMetaboxesActions($entity);
    }

    /**
     * @param WP_Comment $comment
     */
    protected function onCommentSave(WP_Comment $comment)
    {
        // This check allows to edit post object inside BoxAction::save() without recursion.
        if ($this->saving === 'WP_Comment') {
            return;
        }

        $entity = new Entity($comment);
        $this->saveMetaboxesActions($entity);
    }

    /**
     * @return void
     * @throws AuthFactoryException
     */
    private function saveMetaBoxes()
    {
        $globalsCleared = $this->globalsManipulator->clear();

        foreach ($this->boxes as $boxId => $box) {
            if (!$this->isBoxEnabled($box)) {
                continue;
            }

            $siteId = $box->siteId();
            if (!siteExists($siteId)) {
                $title = $box->createInfo('save', $this->entity)->title();
                // translators: 1 is the site ID, 2 the metabox title
                $message = __(
                    'Site %1$d was not accessible when attempting to save metabox "%2$s".',
                    'multilingualpress'
                );
                $notice = AdminNotice::error(sprintf($message, $siteId, $title))
                    ->withTitle(__('Metabox Not Saved', 'multilingualpress'));

                $this->notices->add($notice);

                continue;
            }
            $this->metaboxUpdater->saveMetaBox($box, $boxId, $this->entity);
        }

        $globalsCleared and $this->globalsManipulator->restore();
    }

    /**
     * Clean up state.
     */
    private function releaseTarget()
    {
        $this->entity = null;
        $this->registeringFor = '';
        $this->boxes = [];
        $this->locked = false;
    }

    /**
     * Add the metaboxes for given entity.
     *
     * @param Entity $entity
     */
    protected function addMetaBoxes(Entity $entity): void
    {
        $this->prepareTarget($entity, Metabox::SHOW);
        do_action(self::ACTION_SHOW_METABOXES, $entity);
        array_walk($this->boxes, [$this, 'addMetabox']);
        $this->releaseTarget();
        do_action(self::ACTION_SHOWED_METABOXES, $entity);
    }

    /**
     * Perform metabox saving actions for given entity.
     *
     * @param Entity $entity
     */
    protected function saveMetaboxesActions(Entity $entity): void
    {
        $this->saving = $entity->type();
        $this->prepareTarget($entity, Metabox::SAVE);
        do_action(self::ACTION_SAVE_METABOXES, $entity);
        $this->saveMetaBoxes();
        do_action(self::ACTION_SAVED_METABOXES, $entity);
        $this->releaseTarget();
        $this->saving = '';
    }
}
