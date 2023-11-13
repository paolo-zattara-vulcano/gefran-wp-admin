<?php

declare(strict_types=1);

use Inpsyde\MultilingualPress\Framework\Service\Container;
use Inpsyde\MultilingualPress\Framework\Factory\NonceFactory;
use Inpsyde\MultilingualPress\Framework\Http\ServerRequest;
use Inpsyde\MultilingualPress\Module\ExternalSites\NavMenu\AjaxHandler;
use Inpsyde\MultilingualPress\Module\ExternalSites\NavMenu\ExternalSiteMenuItemFactory;
use Inpsyde\MultilingualPress\Module\ExternalSites\NavMenu\ExternalSiteMenuItemFactoryInterface;
use Inpsyde\MultilingualPress\Module\ExternalSites\NavMenu\MetaBoxView;
use Inpsyde\MultilingualPress\Module\ExternalSites\ServiceProvider;

(static function (Container $container) {
    $nonceFactory = $container->get(NonceFactory::class);
    $nonce = $nonceFactory->create([ServiceProvider::NONCE_ACTION_FOR_EXTERNAL_SITES_NAV_MENU]);

    /**
     * Configuration for "Select All" link in NavMenu metabox.
     *
     * @return string
     */
    $container->share(
        'multilingualpress.ExternalSites.NavMenu.metabox-select-all-link',
        static function (): string {
            $url = add_query_arg(
                [
                    'languages-tab' => 'all',
                    'selectall' => 1,
                    '_wpnonce' => false,
                    'action' => false,
                    'customlink-tab' => false,
                    'edit-menu-item' => false,
                    'menu-item' => false,
                    'page-tab' => false,
                ]
            );

            return "{$url}#mlp-" . MetaBoxView::ID;
        }
    );

    /**
     * Configuration for submit button attributes in NavMenu metabox.
     */
    $container->share(
        'multilingualpress.ExternalSites.NavMenu.metabox-submit-button-attributes',
        static function () use ($nonce): array {

            $submitAttributes = [
                'id' => MetaBoxView::ID . '-submit',
                'data-action' => AjaxHandler::ACTION,
                'data-external-sites' => '#' . MetaBoxView::ID . ' .menu-item-checkbox',
                'data-select-all' => '#' . MetaBoxView::ID . '-select-all',
                'data-nonce-action' => $nonce->action(),
                'data-nonce' => (string)$nonce,
            ];

            if (empty($GLOBALS['nav_menu_selected_id'])) {
                $submitAttributes['disabled'] = 'disabled';
            }

            return $submitAttributes;
        }
    );

    $container->share(
        MetaBoxView::class,
        static function (Container $container): MetaBoxView {
            return new MetaBoxView(
                $container->get('multilingualpress.ExternalSites.AllExternalSites'),
                $container->get('multilingualpress.ExternalSites.NavMenu.metabox-select-all-link'),
                $container->get('multilingualpress.ExternalSites.NavMenu.metabox-submit-button-attributes')
            );
        }
    );

    $container->share(
        ExternalSiteMenuItemFactory::class,
        static function (): ExternalSiteMenuItemFactoryInterface {
            return new ExternalSiteMenuItemFactory();
        }
    );

    $container->share(
        AjaxHandler::class,
        static function (Container $container) use ($nonce): AjaxHandler {
            return new AjaxHandler(
                $nonce,
                $container->get(ServerRequest::class),
                $container->get(ExternalSiteMenuItemFactory::class),
                $container->get('multilingualpress.ExternalSites.AllExternalSites')
            );
        }
    );
}
)($container); //phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
