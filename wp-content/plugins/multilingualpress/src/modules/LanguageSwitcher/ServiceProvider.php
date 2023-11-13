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

namespace Inpsyde\MultilingualPress\Module\LanguageSwitcher;

use Inpsyde\MultilingualPress\Core\Admin\SiteSettingsRepository;
use Inpsyde\MultilingualPress\Framework\Api\Translations;
use Inpsyde\MultilingualPress\Framework\Module\Module;
use Inpsyde\MultilingualPress\Framework\Module\ModuleManager;
use Inpsyde\MultilingualPress\Framework\Module\ModuleServiceProvider;
use Inpsyde\MultilingualPress\Framework\Service\Container;
use Inpsyde\MultilingualPress\Module\ExternalSites\ServiceProvider as ExternalSitesModule;

final class ServiceProvider implements ModuleServiceProvider
{
    const MODULE_ID = 'language-switcher';

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
                        'Enable Language Switcher Widget.',
                        'multilingualpress'
                    ),
                    'name' => __('Language Switcher', 'multilingualpress'),
                    'active' => true,
                ]
            )
        );
    }

    public function activateModule(Container $container)
    {
        $widget = $container[Widget::class];

        add_action('widgets_init', static function () use ($widget) {
            register_widget($widget);
        });
    }

    public function register(Container $container)
    {
        $container->addService(
            ItemFactory::class,
            static function (): ItemFactory {
                return new ItemFactory();
            }
        );

        $container->addService(
            Model::class,
            static function (Container $container): Model {
                return new Model(
                    $container[Translations::class],
                    $container[ItemFactory::class],
                    $container[SiteSettingsRepository::class],
                    $container->get('languageSwitcher.isExternalSitesModuleActive')
                );
            }
        );

        $container->addService(
            View::class,
            static function (): View {
                return new View();
            }
        );

        $container->addService(
            Widget::class,
            static function (Container $container): Widget {
                $isExternalSitesModuleActive = $container->get('languageSwitcher.isExternalSitesModuleActive');
                return new Widget(
                    $container[Model::class],
                    $container[View::class],
                    $container[ModuleManager::class],
                    $container->get('languageSwitcher.isExternalSitesModuleActive')
                );
            }
        );

        $container->share(
            'languageSwitcher.isExternalSitesModuleActive',
            static function (Container $container): bool {
                $moduleManager = $container->get(ModuleManager::class);
                return $moduleManager->isModuleActive(ExternalSitesModule::MODULE_ID);
            }
        );
    }
}
