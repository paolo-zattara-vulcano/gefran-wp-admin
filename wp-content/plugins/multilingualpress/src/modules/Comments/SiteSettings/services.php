<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\Comments\SiteSettings;

use Inpsyde\MultilingualPress\Core\Admin\SiteSettings;
use Inpsyde\MultilingualPress\Core\Admin\SiteSettingsUpdateRequestHandler;
use Inpsyde\MultilingualPress\Core\PostTypeRepository;
use Inpsyde\MultilingualPress\Framework\Admin\SettingsPageTabData;
use Inpsyde\MultilingualPress\Framework\Admin\SettingsPageTabDataAccess;
use Inpsyde\MultilingualPress\Framework\Admin\SettingsPageView;
use Inpsyde\MultilingualPress\Framework\Api\SiteRelations;
use Inpsyde\MultilingualPress\Framework\Asset\AssetManager;
use Inpsyde\MultilingualPress\Framework\Factory\NonceFactory;
use Inpsyde\MultilingualPress\Framework\Http\ServerRequest;
use Inpsyde\MultilingualPress\Framework\Setting\SettingOption;
use Inpsyde\MultilingualPress\Framework\Setting\Site\SiteSettingMultiView;
use Inpsyde\MultilingualPress\Framework\Setting\Site\SiteSettingsSectionViewModel;
use Inpsyde\MultilingualPress\Framework\Setting\Site\SiteSettingView;
use Inpsyde\MultilingualPress\Framework\Setting\SiteSettingsUpdatable;
use Inpsyde\MultilingualPress\Module\Comments\CommentsCopy\CommentsCopier;
use Inpsyde\MultilingualPress\Framework\Service\Container;

(static function (Container $container) {
    $container->share(
        CommentsSettingsRepository::class,
        static function (): CommentsSettingsRepository {
            return new CommentsSettingsRepository();
        }
    );

    /**
     * Returns the list of Site's comment settings view models.
     *
     * @return CommentSettingViewModel[] The list of comment settings view model instances.
     */
    $container->share(
        'multilingualpress.Comments.SiteSettingsViewModels',
        static function (Container $container): array {
            $commentsSettings = [];

            $supportedPostTypes = $container->get(PostTypeRepository::class)->supportedPostTypes();

            foreach ($supportedPostTypes as $postType) {

                /**
                 * Filters if comments are enabled for given post type
                 *
                 * @param bool $areCommentsEnabledForPostType true if enabled, false if not
                 * @param string $postType The post type name
                 */
                $areCommentsEnabledForPostType = (bool)apply_filters(
                    CommentsSettingsRepository::FILTER_COMMENTS_ENABLED_FOR_POST_TYPE,
                    post_type_supports($postType, 'comments'),
                    $postType
                );

                if (!$areCommentsEnabledForPostType) {
                    continue;
                }

                $commentsSettings[] = new CommentSettingViewModel(
                    [
                        new SettingOption(
                            CommentsSettingsRepository::COMMENTS_TAB_OPTION_COPY_COMMENTS,
                            CommentsSettingsRepository::COMMENTS_TAB_OPTION_COPY_COMMENTS,
                            __('Copy comments to selected sites', 'multilingualpress'),
                            ''
                        ),
                        new SettingOption(
                            CommentsSettingsRepository::COMMENTS_TAB_OPTION_COPY_NEW_COMMENT,
                            CommentsSettingsRepository::COMMENTS_TAB_OPTION_COPY_NEW_COMMENT,
                            __('Create comments on selected sites when new comment is added', 'multilingualpress'),
                            ''
                        ),
                    ],
                    $container->get(SiteRelations::class),
                    $container->get(CommentsSettingsRepository::class),
                    $postType
                );
            }

            return $commentsSettings;
        }
    );

    $container->share(
        'multilingualpress.Comments.SiteSettingsSectionViewModel',
        static function (Container $container): SiteSettingsSectionViewModel {
            return new SiteSettings(
                SiteSettingMultiView::fromViewModels($container->get('multilingualpress.Comments.SiteSettingsViewModels')),
                $container->get(AssetManager::class)
            );
        }
    );

    $container->share(
        CommentSettingsView::class,
        static function (Container $container): SiteSettingView {
            return new CommentSettingsView($container->get('multilingualpress.Comments.SiteSettingsSectionViewModel'));
        }
    );

    /**
     * Configuration for site's comment settings tab.
     */
    $container->share(
        'multilingualpress.Comments.settingsPageData',
        static function (): SettingsPageTabDataAccess {
            return new SettingsPageTabData(
                'multilingualpress-comments-site-settings',
                __('MultilingualPress: Comments', 'multilingualpress'),
                'multilingualpress-comments-site-settings',
                'manage_sites'
            );
        }
    );

    /**
     * Returns the site ID of currently edited settings page.
     */
    $container->share(
        'multilingualpress.Comments.settingsPageSiteId',
        static function (Container $container): int {
            $serverRequest = $container->get(ServerRequest::class);
            return (int)$serverRequest->bodyValue('id', INPUT_REQUEST, FILTER_SANITIZE_NUMBER_INT);
        }
    );

    $container->share(
        CommentSettingsPageView::class,
        static function (Container $container): SettingsPageView {
            return new CommentSettingsPageView(
                $container->get('multilingualpress.Comments.settingsPageData'),
                $container->get(CommentSettingsView::class),
                $container->get('multilingualpress.Comments.settingsPageSiteId'),
                CommentsSettingsRepository::COMMENTS_TAB_UPDATE_ACTION_NAME,
                $container->get(NonceFactory::class)->create(['save_site_comment_settings'])
            );
        }
    );

    $container->share(
        CommentSettingsUpdater::class,
        static function (Container $container): SiteSettingsUpdatable {
            return new CommentSettingsUpdater(
                $container->get(ServerRequest::class),
                $container->get(CommentsCopier::class),
                $container->get(CommentsSettingsRepository::class)
            );
        }
    );

    $container->share(
        'multilingualpress.Comments.SiteSettingsUpdateRequestHandler',
        static function (Container $container): SiteSettingsUpdateRequestHandler {
            return new SiteSettingsUpdateRequestHandler(
                $container->get(CommentSettingsUpdater::class),
                $container->get(ServerRequest::class),
                $container->get(NonceFactory::class)->create([CommentsSettingsRepository::COMMENTS_TAB_NONCE_NAME])
            );
        }
    );
}
)($container); //phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
