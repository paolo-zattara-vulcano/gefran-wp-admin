<?php

declare(strict_types=1);

use Inpsyde\MultilingualPress\Database\Table\ExternalSitesTable;
use Inpsyde\MultilingualPress\Framework\Admin\PersistentAdminNotices;
use Inpsyde\MultilingualPress\Framework\Module\ModuleManager;
use Inpsyde\MultilingualPress\Framework\Service\Container;
use Inpsyde\MultilingualPress\Framework\Factory\NonceFactory;
use Inpsyde\MultilingualPress\Framework\Http\ServerRequest;
use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSitesRepository\ExternalSitesRepository;
use Inpsyde\MultilingualPress\Module\ExternalSites\Settings\PageView;
use Inpsyde\MultilingualPress\Module\ExternalSites\Settings\RequestHandler;
use Inpsyde\MultilingualPress\Module\ExternalSites\Settings\TableFormView;
use Inpsyde\MultilingualPress\Module\Redirect\ServiceProvider as RedirectModule;
use Inpsyde\MultilingualPress\Module\ExternalSites\ServiceProvider as ExternalSitesModule;
use Inpsyde\MultilingualPress\SiteFlags\Core\Admin\SiteMenuLanguageStyleSetting;
use Inpsyde\MultilingualPress\SiteFlags\ServiceProvider as SiteFlagsModule;

(static function (Container $container) {

    $container->share(
        PageView::class,
        static function (Container $container): PageView {
            return new PageView(
                $container->get(NonceFactory::class)->create(['save_external_sites']),
                $container->get(ServerRequest::class),
                $container->get(TableFormView::class)
            );
        }
    );

    /**
     * Configuration for external sites display styles.
     *
     * @psalm-type DisplayStyles = array{flag_and_text?: string, only_language: string, only_flag?: string}
     *
     * @psalm-return DisplayStyles
     */
    $container->share(
        ExternalSitesModule::CONFIGURATION_NAME_FOR_EXTERNAL_SITE_DISPLAY_STYLES,
        static function (Container $container): array {
            $moduleManager = $container->get(ModuleManager::class);
            $displayStyle = [SiteMenuLanguageStyleSetting::ONLY_LANGUAGES => __('Only Languages', 'multilingualpress')];

            if ($moduleManager->isModuleActive(SiteFlagsModule::MODULE_ID)) {
                $displayStyle = array_merge($displayStyle, [
                    SiteMenuLanguageStyleSetting::FLAG_AND_LANGUAGES => __('Flags and Languages', 'multilingualpress'),
                    SiteMenuLanguageStyleSetting::ONLY_FLAGS => __('Only Flags', 'multilingualpress'),
                ]);
            }
            return $displayStyle;
        }
    );

    /**
     * Configuration for table form view columns.
     *
     * @psalm-type Attributes = array{
     *      class?: string,
     *      size?: int
     * }
     * @psalm-type Column = array{
     *      header: string,
     *      type: string,
     *      attributes: Attributes,
     *      options?: array<string, string>
     * }
     * @psalm-type ColumnName = string
     *
     * @psalm-return array<ColumnName, Column>
     */
    $container->share(
        'multilingualpress.ExternalSites.TableFormColumns',
        static function (Container $container): array {
            $moduleManager = $container->get(ModuleManager::class);
            $columns = [
                ExternalSitesTable::COLUMN_SITE_URL => [
                    'header' => sprintf("%s*", esc_html__('Site Url', 'multilingualpress')),
                    'type' => 'text',
                    'attributes' => [
                        'class' => ExternalSitesTable::COLUMN_SITE_URL,
                        'size' => 20,
                    ],
                ],
                ExternalSitesTable::COLUMN_SITE_LANGUAGE_NAME => [
                    'header' => sprintf("%s*", esc_html__('Site Language Name', 'multilingualpress')),
                    'type' => 'text',
                    'attributes' => [
                        'class' => ExternalSitesTable::COLUMN_SITE_LANGUAGE_NAME,
                        'size' => 20,
                    ],
                ],
                ExternalSitesTable::COLUMN_SITE_LANGUAGE_LOCALE => [
                    'header' => sprintf("%s*", esc_html__('Site Language Locale', 'multilingualpress')),
                    'type' => 'text',
                    'attributes' => [
                        'class' => ExternalSitesTable::COLUMN_SITE_LANGUAGE_LOCALE,
                        'size' => 20,
                    ],
                ],
                ExternalSitesTable::COLUMN_ENABLE_HREFLANG => [
                    'header' => esc_html__('Enable Hreflang', 'multilingualpress'),
                    'type' => 'checkbox',
                    'attributes' => ['class' => ExternalSitesTable::COLUMN_ENABLE_HREFLANG],
                ],
                ExternalSitesTable::COLUMN_DISPLAY_STYLE => [
                    'header' => esc_html__('Display Style', 'multilingualpress'),
                    'type' => 'select',
                    'attributes' => ['class' => ExternalSitesTable::COLUMN_DISPLAY_STYLE],
                    'options' => $container->get(ExternalSitesModule::CONFIGURATION_NAME_FOR_EXTERNAL_SITE_DISPLAY_STYLES),
                ],
            ];

            if ($moduleManager->isModuleActive(RedirectModule::MODULE_ID)) {
                $columns[ExternalSitesTable::COLUMN_REDIRECT] = [
                    'header' => esc_html__('Enable Automatic Redirect', 'multilingualpress'),
                    'type' => 'checkbox',
                    'attributes' => ['class' => ExternalSitesTable::COLUMN_REDIRECT],
                ];
            }

            return $columns;
        }
    );

    $container->share(
        TableFormView::class,
        static function (Container $container): TableFormView {
            return new TableFormView(
                $container->get(ExternalSitesRepository::class),
                $container->get('multilingualpress.ExternalSites.TableFormColumns')
            );
        }
    );

    $container->share(
        RequestHandler::class,
        static function (Container $container): RequestHandler {
            return new RequestHandler(
                $container->get(ServerRequest::class),
                $container->get(NonceFactory::class)->create(['save_external_sites']),
                $container->get(ExternalSitesRepository::class),
                $container->get(PersistentAdminNotices::class)
            );
        }
    );
}
)($container); //phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
