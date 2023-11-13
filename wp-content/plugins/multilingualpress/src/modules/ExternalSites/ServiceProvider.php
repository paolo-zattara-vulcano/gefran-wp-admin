<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\ExternalSites;

use Inpsyde\MultilingualPress\Asset\AssetFactory;
use Inpsyde\MultilingualPress\Core\Locations;
use Inpsyde\MultilingualPress\Database\Table\ExternalSitesTable;
use Inpsyde\MultilingualPress\Framework\Admin\SettingsPage;
use Inpsyde\MultilingualPress\Framework\Asset\AssetException;
use Inpsyde\MultilingualPress\Framework\Asset\AssetManager;
use Inpsyde\MultilingualPress\Framework\Http\ServerRequest;
use Inpsyde\MultilingualPress\Framework\Integration\Integration;
use Inpsyde\MultilingualPress\Framework\Module\Module;
use Inpsyde\MultilingualPress\Framework\Module\ModuleManager;
use Inpsyde\MultilingualPress\Framework\Module\ModuleServiceProvider;
use Inpsyde\MultilingualPress\Framework\PluginProperties;
use Inpsyde\MultilingualPress\Framework\Service\Container;
use Inpsyde\MultilingualPress\Framework\Service\Exception\LateAccessToNotSharedService;
use Inpsyde\MultilingualPress\Framework\Service\Exception\NameNotFound;
use Inpsyde\MultilingualPress\Framework\WordpressContext;
use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSite\ExternalSiteFactory;
use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSite\ExternalSiteFactoryInterface;
use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSite\ExternalSiteInterface;
use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSitesRepository\ExternalSitesRepository;
use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSitesRepository\ExternalSitesRepositoryInterface;
use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSitesMetaBox\ExternalSitesMetaBoxView;
use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSitesMetaBox\ExternalSitesMetaBoxViewInterface;
use Inpsyde\MultilingualPress\Module\ExternalSites\Integrations\Flags\ExternalSiteFlagFactory;
use Inpsyde\MultilingualPress\Module\ExternalSites\Integrations\Flags\ExternalSiteFlagFactoryInterface;
use Inpsyde\MultilingualPress\Module\ExternalSites\NavMenu\AjaxHandler;
use Inpsyde\MultilingualPress\Module\ExternalSites\NavMenu\ExternalSiteMenuItemFactory;
use Inpsyde\MultilingualPress\Module\ExternalSites\NavMenu\MetaBoxView as NavMenuMetaBoxView;
use Inpsyde\MultilingualPress\Module\ExternalSites\Settings\PageView;
use Inpsyde\MultilingualPress\Module\ExternalSites\Settings\RequestHandler;
use Inpsyde\MultilingualPress\SiteFlags\Core\Admin\SiteMenuLanguageStyleSetting;
use Inpsyde\MultilingualPress\SiteFlags\ServiceProvider as SiteFlagsModule;
use Throwable;
use WP_Post;
use WP_Screen;
use wpdb;

use function Inpsyde\MultilingualPress\isWpDebugMode;
use function Inpsyde\MultilingualPress\wpHookProxy;

class ServiceProvider implements ModuleServiceProvider
{
    public const MODULE_ID = 'external-sites';
    public const NONCE_ACTION_FOR_EXTERNAL_SITES_NAV_MENU = 'add_external_sites_to_nav_menu';
    public const CONFIGURATION_NAME_FOR_EXTERNAL_SITE_KEYWORD = 'multilingualpress.ExternalSites.ExternalSiteKeyWord';
    public const CONFIGURATION_NAME_FOR_EXTERNAL_SITE_DISPLAY_STYLES = 'multilingualpress.ExternalSites.DisplayStyle';
    public const CONFIGURATION_NAME_FOR_FLAGS_FOLDER_PATH = 'multilingualpress.FlagsFolderPath';

    /**
     * @inheritdoc
     */
    public function registerModule(ModuleManager $moduleManager): bool
    {
        return $moduleManager->register(
            new Module(
                self::MODULE_ID,
                [
                    'description' => __(
                        'Enable External Sites module to be able to use external sites as a translation.',
                        'multilingualpress'
                    ),
                    'name' => __('External Sites', 'multilingualpress'),
                    'active' => false,
                ]
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function activateModule(Container $container)
    {
        $this->activateModuleForAdmin($container);

        $externalSitesRepository = $container->get(ExternalSitesRepository::class);
        $moduleManager = $container->get(ModuleManager::class);
        $isSiteFlagsModuleActive = $moduleManager->isModuleActive(SiteFlagsModule::MODULE_ID);
        $externalSiteFlagImageTagFactory = $container->get(ExternalSiteFlagFactory::class);

        $this->filterExternalSiteMenuItem($externalSitesRepository, $isSiteFlagsModuleActive, $externalSiteFlagImageTagFactory);

        $externalSitesIntegrations = $container->get('multilingualpress.ExternalSites.Integrations');
        foreach ($externalSitesIntegrations as $integration) {
            assert($integration instanceof Integration);
            $integration->integrate();
        }
    }

    /**
     * Performs various tasks when is in admin screen on module activation.
     *
     * @param Container $container
     * @throws LateAccessToNotSharedService
     * @throws NameNotFound
     */
    protected function activateModuleForAdmin(Container $container)
    {
        if (!is_admin()) {
            return;
        }

        $externalSitesPage = SettingsPage::withParent(
            SettingsPage::ADMIN_NETWORK,
            SettingsPage::PARENT_MULTILINGUALPRESS,
            __('External Sites', 'multilingualpress'),
            __('External Sites', 'multilingualpress'),
            'manage_network_options',
            'external-sites',
            $container[PageView::class]
        );

        add_action(
            'admin_post_' . RequestHandler::ACTION,
            [$container[RequestHandler::class], 'handlePostRequest']
        );

        add_action('plugins_loaded', [$externalSitesPage, 'register']);

        $assetManager = $container->get(AssetManager::class);
        $assetFactory = $container->get('multilingualpress.externalSites.assetsFactory');
        $this->enqueueAssets($assetManager, $assetFactory);

        $metaBoxView = $container->get(ExternalSitesMetaBoxView::class);
        $externalSites = $container->get('multilingualpress.ExternalSites.AllExternalSites');
        $this->renderMetaBoxes($externalSites, $metaBoxView);

        $request = $container->get(ServerRequest::class);
        $this->saveMetaBoxes($request);

        $wordpressContext = $container->get(WordpressContext::class);
        $navMenuMetaBoxView = $container->get(NavMenuMetaBoxView::class);
        $wpdb = $container[wpdb::class];

        add_action(
            'admin_init',
            static function () use ($navMenuMetaBoxView, $wordpressContext) {
                if ($wordpressContext->isType(WordPressContext::TYPE_CUSTOMIZER)) {
                    return;
                }

                add_meta_box(
                    'mlp-external-sites',
                    esc_html__('MultilingualPress: External Sites', 'multilingualpress'),
                    [$navMenuMetaBoxView, 'render'],
                    'nav-menus',
                    'side',
                    'low'
                );
            }
        );

        add_action(
            'wp_ajax_' . AjaxHandler::ACTION,
            [$container[AjaxHandler::class], 'handle']
        );

        $this->filterMenuItems($wpdb);
    }

    /**
     * @inheritdoc
     *
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
     */
    public function register(Container $container)
    {
        // phpcs:enable

        $container->share(
            self::CONFIGURATION_NAME_FOR_FLAGS_FOLDER_PATH,
            static function (): string {
                return '/resources/images/flags';
            }
        );

        $container->share(
            self::CONFIGURATION_NAME_FOR_EXTERNAL_SITE_KEYWORD,
            static function (): string {
                return __('external', 'multilingualpress');
            }
        );

        $container->share(
            ExternalSiteFactory::class,
            static function (): ExternalSiteFactoryInterface {
                return new ExternalSiteFactory();
            }
        );

        $container->share(
            ExternalSitesRepository::class,
            static function (Container $container): ExternalSitesRepositoryInterface {
                return new ExternalSitesRepository(
                    $container->get(wpdb::class),
                    $container->get(ExternalSitesTable::class),
                    $container->get(ExternalSiteFactory::class),
                    [
                        ExternalSitesTable::COLUMN_SITE_LANGUAGE_NAME,
                        ExternalSitesTable::COLUMN_SITE_URL,
                        ExternalSitesTable::COLUMN_SITE_LANGUAGE_LOCALE,
                    ]
                );
            }
        );

        $container->share(
            'multilingualpress.externalSites.assetsFactory',
            static function (Container $container): AssetFactory {
                $pluginProperties = $container[PluginProperties::class];

                $locations = new Locations();
                $locations
                    ->add(
                        'css',
                        $pluginProperties->dirPath() . 'src/modules/ExternalSites/public/css',
                        $pluginProperties->dirUrl() . 'src/modules/ExternalSites/public/css'
                    )
                    ->add(
                        'js',
                        $pluginProperties->dirPath() . 'src/modules/ExternalSites/public/js',
                        $pluginProperties->dirUrl() . 'src/modules/ExternalSites/public/js'
                    );

                return new AssetFactory($locations);
            }
        );

        /**
         * will return all the existing external sites
         *
         * @return ExternalSiteInterface[] The list of all existing external sites.
         */
        $container->share(
            'multilingualpress.ExternalSites.AllExternalSites',
            static function (Container $container): array {
                $externalSitesRepository = $container->get(ExternalSitesRepository::class);
                return $externalSitesRepository->allExternalSites();
            }
        );

        $container->share(
            ExternalSitesMetaBoxView::class,
            static function (): ExternalSitesMetaBoxViewInterface {
                return new ExternalSitesMetaBoxView();
            }
        );

        $moduleDirPath = __DIR__ ;

        require $moduleDirPath . '/Settings/services.php';
        require $moduleDirPath . '/NavMenu/services.php';
        require $moduleDirPath . '/Integrations/services.php';
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
            ->registerStyle(
                $assetFactory->createInternalStyle(
                    'multilingualpress-external-sites',
                    'admin.min.css'
                )
            )
            ->registerScript(
                $assetFactory->createInternalScript(
                    'multilingualpress-external-sites',
                    'admin.min.js',
                    ['multilingualpress-admin']
                )
            );

        try {
            $assetManager->enqueueStyle('multilingualpress-external-sites');
            $assetManager->enqueueScriptWithData(
                'multilingualpress-external-sites',
                'externalSites',
                [
                    'newExternalSiteButtonLabel' => esc_html__('New External Site', 'multilingualpress'),
                    'externalSiteDeleteTableHeadLabel' => esc_html__('Delete', 'multilingualpress'),
                    'externalSiteUndoDeleteButtonLabel' => esc_html__(
                        'Undo Delete',
                        'multilingualpress'
                    ),
                    'externalSiteDeleteButtonLabel' => esc_html__(
                        'Delete External Site',
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

    /**
     * Renders the MetaBoxes for given external sites.
     *
     * @param ExternalSiteInterface[] $externalSites The list of external sites.
     * @param ExternalSitesMetaBoxViewInterface $externalSitesMetaBoxView
     * phpcs:disable Inpsyde.CodeQuality.NestingLevel.High
     */
    protected function renderMetaBoxes(array $externalSites, ExternalSitesMetaBoxViewInterface $externalSitesMetaBoxView)
    {
        // phpcs:enable

        if (empty($externalSites)) {
            return;
        }

        add_action('current_screen', static function (WP_Screen $screen) use ($externalSites, $externalSitesMetaBoxView) {

            if (empty($screen->post_type)) {
                return;
            }

            add_action(
                'add_meta_boxes',
                static function (string $postType, WP_Post $post) use ($screen, $externalSites, $externalSitesMetaBoxView) {
                    $boxSuffix = 'multilingualpress_external_sites_post_translation_metabox';
                    $postId = (int)$post->ID;

                    add_meta_box(
                        $boxSuffix,
                        __('External Sites', 'multilingualpress'),
                        static function () use ($externalSites, $externalSitesMetaBoxView, $postId) {
                            $externalSitesMetaBoxView->render($externalSites, $postId);
                        },
                        $screen
                    );
                },
                10,
                2
            );
        });
    }

    /**
     * Saves the requested external sites metabox values.
     *
     * @param ServerRequest $request
     */
    protected function saveMetaBoxes(ServerRequest $request)
    {
        add_action(
            'wp_insert_post',
            static function ($postId, WP_Post $post) use ($request) {
                if ($post->post_status === 'trash') {
                    return;
                }

                $multilingualPressRequestValues = $request->bodyValue(
                    'multilingualpress',
                    INPUT_POST,
                    FILTER_DEFAULT,
                    FILTER_REQUIRE_ARRAY
                );

                $externalSitesRequestValues = $multilingualPressRequestValues['external-sites'] ?? [];

                if (empty($externalSitesRequestValues)) {
                    return;
                }

                update_post_meta($postId, ExternalSitesMetaBoxView::META_NAME, $externalSitesRequestValues);
            },
            10,
            2
        );
    }

    /**
     * Filters the external site menu item on frontend.
     *
     * @param ExternalSitesRepository $externalSitesRepository
     * @param bool $isSiteFlagsModuleActive true if the site flags module is active, otherwise false.
     * @param ExternalSiteFlagFactoryInterface $externalSiteFlagImageTagFactory
     * @throws Throwable
     * phpcs:disable Inpsyde.CodeQuality.NestingLevel.High
     */
    protected function filterExternalSiteMenuItem(
        ExternalSitesRepository $externalSitesRepository,
        bool $isSiteFlagsModuleActive,
        ExternalSiteFlagFactoryInterface $externalSiteFlagImageTagFactory
    ): void {

        add_filter(
            'wp_nav_menu_objects',
            wpHookProxy(
                static function (array $items) use ($externalSitesRepository, $isSiteFlagsModuleActive, $externalSiteFlagImageTagFactory): array {

                    foreach ($items as $item) {
                        $menuItemType = $item->object ?? '';

                        if ($menuItemType !== 'mlp_external_site') {
                            continue;
                        }

                        $menuItemExternalSiteId = get_post_meta($item->ID, ExternalSiteMenuItemFactory::META_KEY_EXTERNAL_SITE_ID, true);

                        if (empty($menuItemExternalSiteId)) {
                            continue;
                        }

                        $context = new WordpressContext();

                        $externalSitesMeta = (array)get_post_meta($context->queriedObjectId(), ExternalSitesMetaBoxView::META_NAME, true);
                        $currentExternalSiteMeta = $externalSitesMeta[$menuItemExternalSiteId] ?? [];

                        $currentExternalSite = $externalSitesRepository->externalSiteBy(ExternalSitesTable::COLUMN_ID, $menuItemExternalSiteId);
                        $siteMetaUrl = $currentExternalSiteMeta['url'] ?? '';

                        $item->url = trim($siteMetaUrl) ?: $currentExternalSite->siteUrl();

                        if ($isSiteFlagsModuleActive) {
                            $displayStyle = $currentExternalSite->displayStyle();
                            $title = $item->title;

                            $useFlags = [
                                SiteMenuLanguageStyleSetting::FLAG_AND_LANGUAGES,
                                SiteMenuLanguageStyleSetting::ONLY_FLAGS,
                            ];

                            $flagImageTag = $externalSiteFlagImageTagFactory->createFlagImageTag($currentExternalSite);

                            if ($displayStyle === SiteMenuLanguageStyleSetting::ONLY_FLAGS && $flagImageTag) {
                                $title = "<span class=\"screen-reader-text\">{$item->title}</span>";
                            }

                            if (in_array($displayStyle, $useFlags, true)) {
                                $item->title = $flagImageTag ? $flagImageTag . $title : $title;
                            }
                        }
                    }

                    return $items;
                }
            )
        );
    }

    /**
     * Filters the menu items for external sites.
     *
     * @param wpdb $wpdb
     */
    protected function filterMenuItems(wpdb $wpdb): void
    {
        add_filter(
            'wp_setup_nav_menu_item',
            static function ($item) {
                if (!isset($item->object) || $item->object !== 'mlp_external_site') {
                    return $item;
                }

                $item->type_label = esc_html__('External Site', 'multilingualpress');

                return $item;
            }
        );

        add_action(RequestHandler::ACTION_AFTER_EXTERNAL_SITE_IS_DELETED, static function (int $deletedExternalSiteId) use ($wpdb) {
            //phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
            //phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

            $siteIds = (array)get_sites(['fields' => 'ids']);

            if (!$siteIds) {
                return;
            }

            foreach ($siteIds as $siteId) {
                switch_to_blog($siteId);

                $postSqlFormat = "SELECT p.ID FROM {$wpdb->posts} p ";
                $postSqlFormat .= "INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id ";
                $postSqlFormat .= 'WHERE pm.meta_key = %s AND pm.meta_value = %s';

                $postSql = $wpdb->prepare(
                    $postSqlFormat,
                    ExternalSiteMenuItemFactory::META_KEY_EXTERNAL_SITE_ID,
                    $deletedExternalSiteId
                );

                foreach ($wpdb->get_col($postSql) as $postId) {
                    wp_delete_post($postId, true);
                }

                restore_current_blog();
            }
        });
    }
}
