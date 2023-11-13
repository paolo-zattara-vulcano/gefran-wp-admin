<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\ExternalSites\Integrations;

use Inpsyde\MultilingualPress\Framework\Integration\Integration;
use Inpsyde\MultilingualPress\Framework\Module\ModuleManager;
use Inpsyde\MultilingualPress\Framework\Service\Container;
use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSitesRepository\ExternalSitesRepository;
use Inpsyde\MultilingualPress\Module\ExternalSites\Integrations\Flags\ExternalSiteFlagFactory;
use Inpsyde\MultilingualPress\Module\ExternalSites\Integrations\Flags\ExternalSiteFlagFactoryInterface;
use Inpsyde\MultilingualPress\Module\ExternalSites\Integrations\Redirect\ExternalSiteRedirectTargetFactory;
use Inpsyde\MultilingualPress\Module\ExternalSites\Integrations\Redirect\ExternalSiteRedirectTargetFactoryInterface;
use Inpsyde\MultilingualPress\Module\ExternalSites\Integrations\Redirect\Fallback\ExternalRedirectFallbackViewRenderer;
use Inpsyde\MultilingualPress\Module\ExternalSites\Integrations\Redirect\RedirectIntegration;
use Inpsyde\MultilingualPress\Module\ExternalSites\ServiceProvider;
use Inpsyde\MultilingualPress\Module\LanguageSwitcher\ItemFactory as LanguageSwitcherItemFactory;
use Inpsyde\MultilingualPress\Module\Redirect\LanguageNegotiator;
use Inpsyde\MultilingualPress\Module\Redirect\Settings\Repository;
use Inpsyde\MultilingualPress\Module\Redirect\Settings\ViewRenderer;
use Inpsyde\MultilingualPress\SiteFlags\ServiceProvider as SiteFlagsModule;

(static function (Container $container) {
    $container->share(
        ExternalSiteRedirectTargetFactory::class,
        static function (): ExternalSiteRedirectTargetFactoryInterface {
            return new ExternalSiteRedirectTargetFactory();
        }
    );

    $container->share(
        ExternalRedirectFallbackViewRenderer::class,
        static function (Container $container): ViewRenderer {
            return new ExternalRedirectFallbackViewRenderer(
                $container->get('multilingualpress.ExternalSites.AllExternalSites'),
                $container->get(Repository::class)
            );
        }
    );

    $container->share(
        ExternalSiteFlagFactory::class,
        static function (Container $container): ExternalSiteFlagFactoryInterface {
            $flagsProperties = $container->get('siteFlagsProperties');
            return new ExternalSiteFlagFactory(
                $flagsProperties['pluginPath'],
                $flagsProperties['pluginUrl'],
                $container->get(ServiceProvider::CONFIGURATION_NAME_FOR_FLAGS_FOLDER_PATH)
            );
        }
    );

    /**
     * Configuration for all external sites module integrations.
     *
     * @return Integration[] The list of all external sites module integrations.
     */
    $container->share(
        'multilingualpress.ExternalSites.Integrations',
        static function (Container $container): array {
            $moduleManager = $container->get(ModuleManager::class);
            return [
                new RedirectIntegration(
                    $container->get('multilingualpress.ExternalSites.AllExternalSites'),
                    $container->get(LanguageNegotiator::class),
                    $container->get(ExternalSiteRedirectTargetFactory::class),
                    $container->get(Repository::class),
                    $container->get(ExternalRedirectFallbackViewRenderer::class),
                    $container->get(ExternalSitesRepository::class)
                ),
                new QuickLinksIntegration(
                    $container->get('multilingualpress.ExternalSites.AllExternalSites')
                ),
                new LanguageSwitcherWidgetIntegration(
                    $container->get('multilingualpress.ExternalSites.AllExternalSites'),
                    $container->get(LanguageSwitcherItemFactory::class),
                    $container->get(ExternalSiteFlagFactory::class),
                    $container->get(ServiceProvider::CONFIGURATION_NAME_FOR_EXTERNAL_SITE_KEYWORD),
                    $moduleManager->isModuleActive(SiteFlagsModule::MODULE_ID)
                ),
                new HreflangIntegration(
                    $container->get('multilingualpress.ExternalSites.AllExternalSites'),
                    [
                        'link' => [
                            'href' => true,
                            'hreflang' => true,
                            'rel' => true,
                        ],
                    ]
                ),
            ];
        }
    );
}
)($container); //phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
