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

namespace Inpsyde\MultilingualPress\TranslationUi;

use Inpsyde\MultilingualPress\Api\ContentRelationshipMeta;
use Inpsyde\MultilingualPress\Core\Entity\ActivePostTypes;
use Inpsyde\MultilingualPress\Core\Entity\ActiveTaxonomies;
use Inpsyde\MultilingualPress\Core\PostTypeRepository;
use Inpsyde\MultilingualPress\Core\TaxonomyRepository;
use Inpsyde\MultilingualPress\Framework\Admin\Metabox\MetaboxAuthFactory;
use Inpsyde\MultilingualPress\Framework\Admin\Metabox\MetaboxUpdater;
use Inpsyde\MultilingualPress\Framework\Admin\Metabox\PostMetaboxRendererInterface;
use Inpsyde\MultilingualPress\Framework\Auth\EntityAuthFactory;
use Inpsyde\MultilingualPress\Framework\Database\Exception\NonexistentTable;
use Inpsyde\MultilingualPress\Framework\Entity;
use Inpsyde\MultilingualPress\Framework\Admin\Metabox\Metaboxes;
use Inpsyde\MultilingualPress\Framework\Admin\PersistentAdminNotices;
use Inpsyde\MultilingualPress\Framework\Api\ContentRelations;
use Inpsyde\MultilingualPress\Framework\Api\SiteRelations;
use Inpsyde\MultilingualPress\Framework\Asset\AssetException;
use Inpsyde\MultilingualPress\Framework\Asset\AssetManager;
use Inpsyde\MultilingualPress\Framework\Factory\NonceFactory;
use Inpsyde\MultilingualPress\Framework\Http\RequestGlobalsManipulator;
use Inpsyde\MultilingualPress\Framework\Http\ServerRequest;
use Inpsyde\MultilingualPress\Framework\Module\ModuleManager;
use Inpsyde\MultilingualPress\Framework\Service\BootstrappableServiceProvider;
use Inpsyde\MultilingualPress\Framework\Service\Container;
use Inpsyde\MultilingualPress\Framework\Service\Exception\LateAccessToNotSharedService;
use Inpsyde\MultilingualPress\Framework\Service\Exception\NameNotFound;
use Inpsyde\MultilingualPress\Framework\Service\Exception\NameOverwriteNotAllowed;
use Inpsyde\MultilingualPress\Framework\Service\Exception\WriteAccessOnLockedContainer;
use Inpsyde\MultilingualPress\TranslationUi\Post;
use Inpsyde\MultilingualPress\TranslationUi\Post\PostRelationSaveHelper;
use Inpsyde\MultilingualPress\TranslationUi\Post\RelationshipContext;
use Throwable;
use WP_Post;
use WP_Screen;

use function Inpsyde\MultilingualPress\isWpDebugMode;
use function Inpsyde\MultilingualPress\wpHookProxy;

/**
 * Service provider for all translation objects.
 */
final class ServiceProvider implements BootstrappableServiceProvider
{
    /**
     * @inheritdoc
     * @param Container $container
     * @throws NameOverwriteNotAllowed
     * @throws WriteAccessOnLockedContainer
     */
    public function register(Container $container)
    {
        $container->addService(
            MetaboxAuthFactory::class,
            static function (Container $container): MetaboxAuthFactory {
                return new MetaboxAuthFactory(
                    $container[EntityAuthFactory::class],
                    $container[NonceFactory::class]
                );
            }
        );

        $container->addService(
            MetaboxUpdater::class,
            static function (Container $container): MetaboxUpdater {
                return new MetaboxUpdater(
                    $container[ServerRequest::class],
                    $container[PersistentAdminNotices::class],
                    $container[MetaboxAuthFactory::class]
                );
            }
        );

        $container->addService(
            Metaboxes::class,
            static function (Container $container): Metaboxes {
                return new Metaboxes(
                    $container[RequestGlobalsManipulator::class],
                    $container[PersistentAdminNotices::class],
                    $container[MetaboxUpdater::class],
                    $container[PostTypeRepository::class],
                    $container->get(ModuleManager::class)
                );
            }
        );

        $this->registerForPost($container);
        $this->registerForTerm($container);

        $container->share(
            MetaboxFieldsHelperFactory::class,
            static function (): MetaboxFieldsHelperFactoryInterface {
                return new MetaboxFieldsHelperFactory();
            }
        );
    }

    /**
     * @param Container $container
     * @throws NameOverwriteNotAllowed
     * @throws WriteAccessOnLockedContainer
     */
    private function registerForTerm(Container $container)
    {
        $container->share(
            Term\RelationshipPermission::class,
            static function (Container $container): Term\RelationshipPermission {
                return new Term\RelationshipPermission($container[ContentRelations::class]);
            }
        );

        $container->addService(
            Term\Ajax\ContextBuilder::class,
            static function (Container $container): Term\Ajax\ContextBuilder {
                return new Term\Ajax\ContextBuilder($container[ServerRequest::class]);
            }
        );

        $container->addService(
            Term\TableList::class,
            static function (Container $container): Term\TableList {
                return new Term\TableList($container[ContentRelations::class]);
            }
        );

        $container->share(
            Term\Ajax\Search::class,
            static function (Container $container): Term\Ajax\Search {
                return new Term\Ajax\Search(
                    $container[ServerRequest::class],
                    $container[Term\Ajax\ContextBuilder::class]
                );
            }
        );

        $container->share(
            Term\Ajax\RelationshipUpdater::class,
            static function (Container $container): Term\Ajax\RelationshipUpdater {
                return new Term\Ajax\RelationshipUpdater(
                    $container[ServerRequest::class],
                    $container[Term\Ajax\ContextBuilder::class],
                    $container[ContentRelations::class],
                    $container[ActiveTaxonomies::class],
                    $container[Term\RelationshipPermission::class]
                );
            }
        );
    }

    /**
     * @param Container $container
     * @throws NameOverwriteNotAllowed
     * @throws WriteAccessOnLockedContainer
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
     */
    private function registerForPost(Container $container)
    {
        // phpcs:enable

        $container->share(
            Post\RelationshipPermission::class,
            static function (Container $container): Post\RelationshipPermission {
                return new Post\RelationshipPermission($container[ContentRelations::class]);
            }
        );

        $container->addService(
            Post\Ajax\ContextBuilder::class,
            static function (Container $container): Post\Ajax\ContextBuilder {
                return new Post\Ajax\ContextBuilder($container[ServerRequest::class]);
            }
        );

        $container->addService(
            Post\TableList::class,
            static function (Container $container): Post\TableList {
                return new Post\TableList($container[ContentRelations::class]);
            }
        );

        $container->share(
            Post\Ajax\Search::class,
            static function (Container $container): Post\Ajax\Search {
                return new Post\Ajax\Search(
                    $container[ServerRequest::class],
                    $container[Post\Ajax\ContextBuilder::class]
                );
            }
        );

        $container->share(
            Post\Ajax\Term::class,
            static function (Container $container): Post\Ajax\Term {
                return new Post\Ajax\Term(
                    $container[ServerRequest::class],
                    $container[Post\Ajax\ContextBuilder::class]
                );
            }
        );

        $container->share(
            Post\Ajax\RelationshipUpdater::class,
            static function (Container $container): Post\Ajax\RelationshipUpdater {
                return new Post\Ajax\RelationshipUpdater(
                    $container[ServerRequest::class],
                    $container[Post\Ajax\ContextBuilder::class],
                    $container[ContentRelations::class],
                    $container[ActivePostTypes::class],
                    $container[Post\RelationshipPermission::class]
                );
            }
        );

        $container->addService(
            Post\PostModifiedDateFilter::class,
            static function (): Post\PostModifiedDateFilter {
                return new Post\PostModifiedDateFilter();
            }
        );

        /**
         * Configuration for relationship metabox renderers.
         */
        $container->share(
            'multilingualpress.TranslationUi.RelationshipMetaBoxRenderers',
            static function (): array {
                return [];
            }
        );

        /**
         * Configuration for name of relationship metaboxes.
         */
        $container->share(
            'multilingualpress.TranslationUi.RelationshipMetaBoxesName',
            static function (): string {
                return 'multilingualpress-relationship-meta';
            }
        );
    }

    /**
     * @inheritdoc
     * @param Container $container
     * @throws Throwable
     * phpcs:disable Generic.Metrics.NestingLevel.TooHigh
     */
    public function bootstrap(Container $container)
    {
        // phpcs:enable

        $this->deleteRelationOnContentDelete($container);

        if (!is_admin()) {
            return;
        }

        $metaboxes = $container[Metaboxes::class];
        $assetManager = $container[AssetManager::class];

        add_action(
            'admin_menu',
            static function () use ($assetManager, $metaboxes) {
                $metaboxes->init();
                try {
                    $assetManager->enqueueStyle('multilingualpress-admin');
                } catch (AssetException $exc) {
                    if (isWpDebugMode()) {
                        throw $exc;
                    }
                }
            }
        );

        add_action(
            Metaboxes::REGISTER_METABOXES,
            function (Metaboxes $metaboxes, Entity $entity) use ($container) {
                if (!$entity->is(WP_Post::class) && !$entity->is(\WP_Term::class)) {
                    return;
                }

                $siteRelations = $container[SiteRelations::class];
                $currentSite = get_current_blog_id();
                $relatedSites = $siteRelations->relatedSiteIds($currentSite);
                if (!$relatedSites) {
                    return;
                }

                foreach ($relatedSites as $relatedSite) {
                    $metaboxes->addBox(
                        ...$this->createBoxes($currentSite, $relatedSite, $container)
                    );
                }
            },
            10,
            2
        );

        $this->bootstrapTablesLists($container);
        $this->bootstrapAjax($container);

        $insertPostData = $container[Post\PostModifiedDateFilter::class];
        add_action(MetaboxUpdater::ACTION_SAVE_METABOX, [$insertPostData, 'enable']);
        add_action(MetaboxUpdater::ACTION_SAVED_METABOX, [$insertPostData, 'disable']);

        $this->bootstrapRelationShipMetaboxes($container);
    }

    /**
     * Delete the relation when a content is permanently deleted
     *
     * @param Container $container
     * @throws NonexistentTable
     */
    private function deleteRelationOnContentDelete(Container $container)
    {
        $contentRelations = $container[ContentRelations::class];

        add_action('delete_term', static function (int $termId) use ($contentRelations) {
            $contentRelations->deleteRelation(
                [get_current_blog_id() => $termId],
                ContentRelations::CONTENT_TYPE_TERM
            );
        });
        add_action('after_delete_post', static function (int $postId) use ($contentRelations) {
            $contentRelations->deleteRelation(
                [get_current_blog_id() => $postId],
                ContentRelations::CONTENT_TYPE_POST
            );
        });
    }

    /**
     * Bootstrap the Table Lists
     *
     * @param Container $container
     * @throws Throwable
     */
    private function bootstrapTablesLists(Container $container)
    {
        $this->bootstrapPostTypeTablesLists($container);
        $this->bootstrapTaxonomyTablesLists($container);
    }

    /**
     * Bootstrap the Post Type table lists
     *
     * @param Container $container
     * @throws Throwable
     */
    private function bootstrapPostTypeTablesLists(Container $container)
    {
        $translatablePostTypes = $container[PostTypeRepository::class]->supportedPostTypes();
        if (!$translatablePostTypes) {
            return;
        }

        $postTableList = $container[Post\TableList::class];
        foreach ($translatablePostTypes as $postType) {
            add_filter(
                "manage_edit-{$postType}_columns",
                wpHookProxy([$postTableList, 'editTranslationColumns']),
                10,
                2
            );
            add_action(
                "manage_{$postType}_posts_custom_column",
                wpHookProxy([$postTableList, 'editTranslationLinks']),
                10,
                2
            );
        }
    }

    /**
     * Bootstrap the Taxonomy table list
     *
     * @param Container $container
     * @throws Throwable
     */
    private function bootstrapTaxonomyTablesLists(Container $container)
    {
        $translatableTaxonomies = $container[TaxonomyRepository::class]->supportedTaxonomies();
        if (!$translatableTaxonomies) {
            return;
        }

        $termTableList = $container[Term\TableList::class];
        foreach ($translatableTaxonomies as $taxonomy) {
            add_filter(
                "manage_edit-{$taxonomy}_columns",
                wpHookProxy([$termTableList, 'editTranslationColumns'])
            );
            add_filter(
                "manage_{$taxonomy}_custom_column",
                wpHookProxy([$termTableList, 'editTranslationLinks']),
                10,
                3
            );
        }
    }

    /**
     * @param int $currentSite
     * @param int $relatedSite
     * @param Container $container
     * @return array
     */
    private function createBoxes(
        int $currentSite,
        int $relatedSite,
        Container $container
    ): array {

        return [
            new Post\Metabox(
                $currentSite,
                $relatedSite,
                $container[ActivePostTypes::class],
                $container[ContentRelations::class],
                $container[Post\RelationshipPermission::class]
            ),
            new Term\Metabox(
                $currentSite,
                $relatedSite,
                $container[ActiveTaxonomies::class],
                $container[ContentRelations::class],
                $container[Term\RelationshipPermission::class]
            ),
        ];
    }

    /**
     * @param Container $container
     */
    private function bootstrapAjax(Container $container)
    {
        add_action(
            'wp_ajax_' . Post\Ajax\Search::ACTION,
            [$container[Post\Ajax\Search::class], 'handle']
        );

        add_action(
            'wp_ajax_' . Post\Ajax\RelationshipUpdater::ACTION,
            [$container[Post\Ajax\RelationshipUpdater::class], 'handle']
        );

        add_action(
            'wp_ajax_' . Term\Ajax\Search::ACTION,
            [$container[Term\Ajax\Search::class], 'handle']
        );

        add_action(
            'wp_ajax_' . Post\Ajax\Term::ACTION,
            [$container[Post\Ajax\Term::class], 'handle']
        );

        add_action(
            'wp_ajax_' . Term\Ajax\RelationshipUpdater::ACTION,
            [$container[Term\Ajax\RelationshipUpdater::class], 'handle']
        );
    }

    /**
     * Bootstraps the functionality for relationship metaboxes.
     *
     * @param Container $container
     * @throws LateAccessToNotSharedService | NameNotFound | NonexistentTable
     */
    protected function bootstrapRelationshipMetaboxes(Container $container): void
    {
        $relationshipMetaBoxRenderers = $container->get('multilingualpress.TranslationUi.RelationshipMetaBoxRenderers');
        $postTypeRepository = $container->get(PostTypeRepository::class);
        $supportedPostTypes = $postTypeRepository->supportedPostTypes();
        $relationshipMetaName = $container->get('multilingualpress.TranslationUi.RelationshipMetaBoxesName');

        /**
         * Adds the relationship metaboxes.
         */
        add_action(
            'current_screen',
            function (WP_Screen $screen) use ($relationshipMetaBoxRenderers, $supportedPostTypes, $relationshipMetaName) {
                if (empty($screen->post_type) || !in_array($screen->post_type, $supportedPostTypes, true)) {
                    return;
                }

                add_action(
                    'add_meta_boxes',
                    function (string $postType, WP_Post $post) use ($screen, $relationshipMetaBoxRenderers, $relationshipMetaName) {
                        $this->renderRelationshipMetaboxes((int)$post->ID, $relationshipMetaBoxRenderers, $screen, $relationshipMetaName);
                    },
                    10,
                    2
                );
            }
        );

        $request = $container->get(ServerRequest::class);
        $contentRelationshipMeta = $container->get(ContentRelationshipMeta::class);

        /**
         * Saves the relationship metaboxes.
         */
        add_action(
            PostRelationSaveHelper::ACTION_AFTER_SAVED_RELATIONS,
            function (RelationshipContext $context, int $relationshipId) use ($request, $relationshipMetaName, $contentRelationshipMeta) {
                $sourcePost = $context->sourcePost();
                if ($sourcePost->post_status === 'trash') {
                    return;
                }

                $relationshipMetas = $request->bodyValue(
                    $relationshipMetaName,
                    INPUT_POST,
                    FILTER_UNSAFE_RAW,
                    FILTER_FORCE_ARRAY
                );

                if (empty($relationshipMetas)) {
                    return;
                }

                foreach ($relationshipMetas as $metaKey => $metaValue) {
                    $metaValue = is_array($metaValue) ? maybe_serialize($metaValue) : (string)$metaValue;
                    $contentRelationshipMeta->updateRelationshipMeta($relationshipId, $metaKey, (string)$metaValue);
                }
            },
            10,
            2
        );
    }

    /**
     * Renders the relationship metaboxes with given name for given screen and post ID.
     *
     * @param int $postId The post ID.
     * @param array<PostMetaboxRendererInterface> $relationshipMetaBoxRenderers The list of relationship metaboxes.
     * @param WP_Screen $screen
     * @param string $relationshipMetaName
     */
    protected function renderRelationshipMetaboxes(
        int $postId,
        array $relationshipMetaBoxRenderers,
        WP_Screen $screen,
        string $relationshipMetaName
    ): void {

        foreach ($relationshipMetaBoxRenderers as $relationshipMetaBoxRenderer) {
            add_meta_box(
                $relationshipMetaName,
                __('MultilingualPress', 'multilingualpress'),
                static function () use ($relationshipMetaBoxRenderer, $postId) {
                    $relationshipMetaBoxRenderer->render($postId);
                },
                $screen,
                'side'
            );
        }
    }
}
