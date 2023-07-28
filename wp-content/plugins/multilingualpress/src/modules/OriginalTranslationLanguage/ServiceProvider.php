<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\OriginalTranslationLanguage;

use Inpsyde\MultilingualPress\Api\ContentRelationshipMeta;
use Inpsyde\MultilingualPress\Framework\Admin\Metabox\PostMetaboxRendererInterface;
use Inpsyde\MultilingualPress\Framework\Integration\Integration;
use Inpsyde\MultilingualPress\Framework\Module\Module;
use Inpsyde\MultilingualPress\Framework\Module\ModuleManager;
use Inpsyde\MultilingualPress\Framework\Module\ModuleServiceProvider;
use Inpsyde\MultilingualPress\Framework\Service\Container;

class ServiceProvider implements ModuleServiceProvider
{
    public const MODULE_ID = 'original_translation_language';

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
                        'Enable the Original Translation Language checkbox on post edit screen.',
                        'multilingualpress'
                    ),
                    'name' => __('Original Translation Language', 'multilingualpress'),
                    'active' => false,
                    'disabled' => false,
                ]
            )
        );
    }

    /**
     * @inheritdoc
     * phpcs:disable Inpsyde.CodeQuality.FunctionLength.TooLong
     */
    public function register(Container $container): void
    {
        // phpcs:enable

        /**
         * Configuration for the keyword to display on frontend.
         */
        $container->share(
            'multilingualpress.OriginalTranslationLanguage.Keyword',
            static function (): string {
                return __('(original)', 'multilingualpress');
            }
        );

        $container->share(
            MetaboxRenderer::class,
            static function (Container $container): PostMetaboxRendererInterface {
                return new MetaboxRenderer(
                    $container->get('multilingualpress.NavMenu.RelatedSites'),
                    __('Original Translation Language', 'multilingualpress'),
                    $container->get('multilingualpress.TranslationUi.RelationshipMetaBoxesName'),
                    $container->get(ContentRelationshipMeta::class)
                );
            }
        );

        /**
         * Configuration for relationship metaboxes.
         */
        $container->extend(
            'multilingualpress.TranslationUi.RelationshipMetaBoxRenderers',
            static function (array $prev, Container $container): array {
                $moduleManager = $container->get(ModuleManager::class);
                if (!$moduleManager->isModuleActive(self::MODULE_ID)) {
                    return [];
                }

                return [$container->get(MetaboxRenderer::class)];
            }
        );

        $moduleDirPath = __DIR__ ;

        require $moduleDirPath . '/Integrations/services.php';
    }

    /**
     * @inheritdoc
     */
    public function activateModule(Container $container): void
    {
        /** @var Integration[] $integrations */
        $integrations = $container->get('multilingualpress.OriginalTranslationLanguage.Integrations');

        foreach ($integrations as $integration) {
            $integration->integrate();
        }
    }
}
