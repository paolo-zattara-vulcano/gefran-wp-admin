<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\ExternalSites\Integrations;

use Inpsyde\MultilingualPress\Api\ContentRelationshipMeta;
use Inpsyde\MultilingualPress\Module\OriginalTranslationLanguage\ServiceProvider as OriginalTranslationLanguageModule;
use Inpsyde\MultilingualPress\Framework\Integration\Integration;
use Inpsyde\MultilingualPress\Framework\Service\Container;
use Inpsyde\MultilingualPress\Module\OriginalTranslationLanguage\Integrations\QuickLinks\QuickLinksIntegration;
use Inpsyde\MultilingualPress\Module\OriginalTranslationLanguage\Integrations\QuickLinks\QuickLinksOriginalLanguageViewModel;
use Inpsyde\MultilingualPress\Module\QuickLinks\Model\ViewModel;
use Inpsyde\MultilingualPress\Module\QuickLinks\Settings\Repository;

(static function (Container $container) {
    $container->share(
        'multilingualpress.OriginalTranslationLanguage.Integrations.QuickLinksSettingValue',
        static function (Container $container): bool {
            $quickLinksSettingRepository = $container->get(Repository::class);

            return (bool)$quickLinksSettingRepository->settingValue(OriginalTranslationLanguageModule::MODULE_ID);
        }
    );

    $container->share(
        QuickLinksOriginalLanguageViewModel::class,
        static function (Container $container): ViewModel {
            return new QuickLinksOriginalLanguageViewModel(
                OriginalTranslationLanguageModule::MODULE_ID,
                Repository::MODULE_SETTINGS,
                __('Select the checkbox to show the "(original)" next to the language.', 'multilingualpress'),
                $container->get('multilingualpress.OriginalTranslationLanguage.Integrations.QuickLinksSettingValue')
            );
        }
    );

    /**
     * Configuration for all integrations for the module.
     *
     * @return Integration[] The list of all integrations for the module.
     */
    $container->share(
        'multilingualpress.OriginalTranslationLanguage.Integrations',
        static function (Container $container): array {
            return [
                new QuickLinksIntegration(
                    $container->get(QuickLinksOriginalLanguageViewModel::class),
                    $container->get(ContentRelationshipMeta::class),
                    $container->get('multilingualpress.OriginalTranslationLanguage.Keyword'),
                    $container->get('multilingualpress.OriginalTranslationLanguage.Integrations.QuickLinksSettingValue')
                ),
            ];
        }
    );
}
)($container); //phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
