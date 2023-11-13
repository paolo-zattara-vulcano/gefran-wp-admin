<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\Comments;

use Inpsyde\MultilingualPress\Asset\AssetFactory;
use Inpsyde\MultilingualPress\Core\Admin\SiteSettingsUpdateRequestHandler;
use Inpsyde\MultilingualPress\Core\Locations;
use Inpsyde\MultilingualPress\Framework\Admin\EditSiteTab;
use Inpsyde\MultilingualPress\Framework\Admin\Metabox\Metaboxes;
use Inpsyde\MultilingualPress\Framework\Admin\SettingsPageTab;
use Inpsyde\MultilingualPress\Framework\Admin\TranslationColumnInterface;
use Inpsyde\MultilingualPress\Framework\Api\ContentRelations;
use Inpsyde\MultilingualPress\Framework\Api\SiteRelations;
use Inpsyde\MultilingualPress\Framework\Asset\AssetException;
use Inpsyde\MultilingualPress\Framework\Asset\AssetManager;
use Inpsyde\MultilingualPress\Framework\Database\Exception\NonexistentTable;
use Inpsyde\MultilingualPress\Framework\Entity;
use Inpsyde\MultilingualPress\Framework\Http\ServerRequest;
use Inpsyde\MultilingualPress\Framework\Module\Exception\ModuleAlreadyRegistered;
use Inpsyde\MultilingualPress\Framework\Module\Module;
use Inpsyde\MultilingualPress\Framework\Module\ModuleManager;
use Inpsyde\MultilingualPress\Framework\Module\ModuleServiceProvider;
use Inpsyde\MultilingualPress\Framework\PluginProperties;
use Inpsyde\MultilingualPress\Framework\Service\Exception\LateAccessToNotSharedService;
use Inpsyde\MultilingualPress\Framework\Service\Exception\NameNotFound;
use Inpsyde\MultilingualPress\Framework\Service\Container;
use Inpsyde\MultilingualPress\Module\Comments\CommentsCopy\CommentsCopier;
use Inpsyde\MultilingualPress\Module\Comments\CommentsCopy\CommentsCopierInterface;
use Inpsyde\MultilingualPress\Module\Comments\RelationshipContext\CommentRelationSaveHelper;
use Inpsyde\MultilingualPress\Module\Comments\RelationshipContext\CommentsRelationshipContextFactory;
use Inpsyde\MultilingualPress\Module\Comments\SiteSettings\CommentSettingsPageView;
use Inpsyde\MultilingualPress\Module\Comments\SiteSettings\CommentsSettingsRepository;
use Inpsyde\MultilingualPress\Module\Comments\TranslationUi\Ajax\AjaxUpdateCommentsRelationshipRequestHandler;
use Inpsyde\MultilingualPress\Module\Comments\TranslationUi\Ajax\AjaxSearchCommentRequestHandler;
use Inpsyde\MultilingualPress\Module\Comments\TranslationUi\CommentMetabox;
use Inpsyde\MultilingualPress\Module\Comments\TranslationUi\CommentsListViewTranslationColumn;
use Inpsyde\MultilingualPress\TranslationUi\MetaboxFieldsHelperFactory;
use Inpsyde\MultilingualPress\Core\ServiceProvider as CoreServiceProvider;
use Throwable;
use WP_Comment;

use function Inpsyde\MultilingualPress\isWpDebugMode;
use function Inpsyde\MultilingualPress\siteNameWithLanguage;

/**
 * Service provider for Comments
 */
class ServiceProvider implements ModuleServiceProvider
{
    public const MODULE_ID = 'mlp-comments';

    /**
     * @inheritDoc
     * @param ModuleManager $moduleManager
     * @return bool
     * @throws ModuleAlreadyRegistered
     */
    public function registerModule(ModuleManager $moduleManager): bool
    {
        return $moduleManager->register(
            new Module(
                self::MODULE_ID,
                [
                    'description' => __('Enable Comments functionality for MultilingualPress.', 'multilingualpress'),
                    'name' => __('Comments', 'multilingualpress'),
                    'active' => true,
                ]
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function register(Container $container)
    {
        // phpcs:enable

        $moduleDirPath = __DIR__ ;

        require $moduleDirPath . '/SiteSettings/services.php';
        require $moduleDirPath . '/RelationshipContext/services.php';
        require $moduleDirPath . '/TranslationUi/services.php';

        $container->share(
            CommentsCopier::class,
            static function (Container $container): CommentsCopierInterface {
                return new CommentsCopier(
                    $container->get(CommentsRelationshipContextFactory::class),
                    $container->get(CommentRelationSaveHelper::class)
                );
            }
        );

        $container->share(
            'multilingualpress.Comments.AssetsFactory',
            static function (Container $container): AssetFactory {
                $pluginProperties = $container->get(PluginProperties::class);

                $locations = new Locations();
                $locations
                    ->add(
                        'js',
                        $pluginProperties->dirPath() . 'src/modules/Comments/public/js',
                        $pluginProperties->dirUrl() . 'src/modules/Comments/public/js'
                    );

                return new AssetFactory($locations);
            }
        );
    }

    /**
     * @inheritdoc
     * @throws LateAccessToNotSharedService
     * @throws NameNotFound|Throwable
     */
    public function activateModule(Container $container)
    {
        $contentRelations = $container->get(ContentRelations::class);

        add_action('deleted_comment', static function (int $commentId) use ($contentRelations) {
            $contentRelations->deleteRelation(
                [get_current_blog_id() => $commentId],
                ContentRelations::CONTENT_TYPE_COMMENT
            );
        });

        if (is_admin()) {
            $this->bootstrapAdmin($container);
            is_network_admin() and $this->bootstrapNetworkAdmin($container);
            return;
        }

        $this->bootstrapFrontEnd($container);
    }

    /**
     * Bootstraps frontend functionality.
     *
     * @param Container $container
     * @throws LateAccessToNotSharedService
     * @throws NameNotFound|Throwable
     */
    protected function bootstrapFrontEnd(Container $container)
    {
        $settingsRepository = $container->get(CommentsSettingsRepository::class);
        $commentsCopier = $container->get(CommentsCopier::class);

        add_action(
            'comment_post',
            static function (int $commentId) use ($settingsRepository, $commentsCopier) {
                $sourceCommentPost = get_comment($commentId);
                $sourceCommentPost = get_post($sourceCommentPost->comment_post_ID);
                $sourceSiteId = get_current_blog_id();

                if (! $sourceCommentPost) {
                    return;
                }

                $siteIds = $settingsRepository->settingOptionValue(
                    CommentsSettingsRepository::COMMENTS_TAB_OPTION_COPY_NEW_COMMENT,
                    $sourceCommentPost->post_type,
                    $sourceSiteId
                );

                if (empty($siteIds)) {
                    return;
                }

                $commentsCopier->copyCommentsToSites($sourceSiteId, [$commentId], $siteIds);
            }
        );
    }

    /**
     * Bootstraps admin functionality.
     *
     * @param Container $container
     * @throws LateAccessToNotSharedService|NameNotFound|NonexistentTable
     */
    protected function bootstrapAdmin(Container $container)
    {
        $siteSettingsUpdateRequestHandler = $container->get('multilingualpress.Comments.SiteSettingsUpdateRequestHandler');
        assert($siteSettingsUpdateRequestHandler instanceof SiteSettingsUpdateRequestHandler);
        $commentsListViewTranslationColumn = $container->get(CommentsListViewTranslationColumn::class);

        add_action(
            'admin_post_' . CommentsSettingsRepository::COMMENTS_TAB_UPDATE_ACTION_NAME,
            [$siteSettingsUpdateRequestHandler, 'handlePostRequest']
        );


        $this->bootstrapMetaboxes($container);
        $this->bootstapTranslationColumnForListView($commentsListViewTranslationColumn);


        $serverRequest = $container->get(ServerRequest::class);
        $searchRequestHandler = $container->get(AjaxSearchCommentRequestHandler::class);

        add_action(
            'wp_ajax_' . AjaxSearchCommentRequestHandler::ACTION,
            static function () use ($searchRequestHandler, $serverRequest) {
                $searchRequestHandler->handle($serverRequest);
            }
        );

        $relationshipRequestHandler = $container->get(AjaxUpdateCommentsRelationshipRequestHandler::class);

        add_action(
            'wp_ajax_' . AjaxUpdateCommentsRelationshipRequestHandler::ACTION,
            static function () use ($relationshipRequestHandler, $serverRequest) {
                $relationshipRequestHandler->handle($serverRequest);
            }
        );

        $assetManager = $container->get(AssetManager::class);
        $assetFactory = $container->get('multilingualpress.Comments.AssetsFactory');
        $this->enqueueAssets($assetManager, $assetFactory);

        add_filter(CoreServiceProvider::FILTER_ADMIN_ALLOWED_SCRIPT_PAGES, static function (array $allowedPages): array {
            $allowedPages[] = 'comment.php';
            return $allowedPages;
        });
    }

    /**
     * Bootstraps Network admin functionality.
     *
     * @param Container $container
     * @throws LateAccessToNotSharedService|NameNotFound
     */
    protected function bootstrapNetworkAdmin(Container $container): void
    {
        $editCommentsSiteTab = new EditSiteTab(
            new SettingsPageTab(
                $container->get('multilingualpress.Comments.settingsPageData'),
                $container->get(CommentSettingsPageView::class)
            )
        );
        $editCommentsSiteTab->register();
    }

    /**
     * Bootstraps the translation metaboxes for comments.
     *
     * @param Container $container
     * @throws LateAccessToNotSharedService|NameNotFound|NonexistentTable
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
     */
    protected function bootstrapMetaboxes(Container $container): void
    {
        // phpcs:enable

        $siteRelations = $container->get(SiteRelations::class);
        $contentRelations = $container->get(ContentRelations::class);
        $metaboxFieldsHelperFactory = $container->get(MetaboxFieldsHelperFactory::class);
        $relationshipContextFactory = $container->get(CommentsRelationshipContextFactory::class);
        $metaboxTabs = $container->get('multilingualpress.Comments.MetaboxTabs');
        $metaboxFields = $container->get('multilingualpress.Comments.MetaboxFields');
        $commentRelationSaveHelper = $container->get(CommentRelationSaveHelper::class);

        add_action(
            Metaboxes::REGISTER_METABOXES,
            static function (
                Metaboxes $metaboxes,
                Entity $entity
            ) use (
                $siteRelations,
                $contentRelations,
                $metaboxFieldsHelperFactory,
                $relationshipContextFactory,
                $metaboxTabs,
                $metaboxFields,
                $commentRelationSaveHelper
            ) {

                if (!$entity->is(WP_Comment::class)) {
                    return;
                }

                $sourceSiteId = get_current_blog_id();
                $relatedSiteIds = $siteRelations->relatedSiteIds($sourceSiteId);

                if (!$relatedSiteIds) {
                    return;
                }

                $sourceCommentId = (int)$entity->prop('comment_ID');
                $sourcePostId = (int)$entity->prop('comment_post_ID');

                if (!$sourcePostId) {
                    return;
                }

                foreach ($relatedSiteIds as $remoteSiteId) {
                    $remotePostId = $contentRelations->contentIdForSite(
                        $sourceSiteId,
                        $sourcePostId,
                        ContentRelations::CONTENT_TYPE_POST,
                        $remoteSiteId
                    );

                    if (!$remotePostId) {
                        continue;
                    }

                    $title = sprintf(
                    /* translators: %s is site name including language */
                        __('Translation for "%s"', 'multilingualpress'),
                        siteNameWithLanguage($remoteSiteId)
                    );

                    $remoteCommentId = $contentRelations->contentIdForSite(
                        $sourceSiteId,
                        $sourceCommentId,
                        ContentRelations::CONTENT_TYPE_COMMENT,
                        $remoteSiteId
                    );

                    $relationshipContext = $relationshipContextFactory->createCommentsRelationshipContext(
                        $sourceSiteId,
                        $remoteSiteId,
                        $sourceCommentId,
                        $remoteCommentId
                    );

                    $metabox = new CommentMetabox(
                        $title,
                        $relationshipContext,
                        $metaboxTabs,
                        $metaboxFields,
                        $metaboxFieldsHelperFactory->createMetaboxFieldsHelper($remoteSiteId),
                        $commentRelationSaveHelper
                    );
                    $metaboxes->addBox($metabox);
                }
            },
            10,
            2
        );
    }

    /**
     * Will add the custom translation column in comments list view admin screen.
     *
     * @param TranslationColumnInterface $translationColumn
     */
    protected function bootstapTranslationColumnForListView(TranslationColumnInterface $translationColumn): void
    {
        add_filter('manage_edit-comments_columns', static function (array $columns) use ($translationColumn): array {
            $columns[$translationColumn->name()] = $translationColumn->title();
            return $columns;
        });

        add_action(
            'manage_comments_custom_column',
            static function (string $columnName, string $commentId) use ($translationColumn): void {
                if ($columnName !== $translationColumn->name()) {
                    return;
                }

                echo wp_kses_post($translationColumn->value((int)$commentId));
            },
            10,
            2
        );
    }

    /**
     * Will enqueue the module assets.
     *
     * @param AssetManager $assetManager
     * @param AssetFactory $assetFactory
     */
    protected function enqueueAssets(AssetManager $assetManager, AssetFactory $assetFactory)
    {
        $assetManager
            ->registerScript(
                $assetFactory->createInternalScript(
                    'multilingualpress-comment-site-settings',
                    'admin.min.js',
                    ['multilingualpress-admin']
                )
            );

        try {
            $assetManager->enqueueScriptWithData(
                'multilingualpress-comment-site-settings',
                'commentSettings',
                [
                    'confirmationMessage' => __(
                        'You are about to copy all the comments of selected post type(s) to the selected site(s). The action cannot be undone',
                        'multilingualpress'
                    ),
                ]
            );
        } catch (AssetException $exc) {
            if (isWpDebugMode()) {
                throw $exc;
            }
        }
    }
}
