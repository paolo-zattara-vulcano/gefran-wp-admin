<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\ExternalSites\NavMenu;

use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSite\ExternalSiteInterface;
use RuntimeException;
use WP_Post;

class ExternalSiteMenuItemFactory implements ExternalSiteMenuItemFactoryInterface
{
    public const META_KEY_EXTERNAL_SITE_ID = '_external_site_id';
    public const META_KEY_ITEM_TYPE = '_menu_item_type';
    protected const ITEM_TYPE = 'mlp_external_site';
    protected const FILTER_MENU_EXTERNAL_SITE_NAME = 'multilingualpress.nav_menu_external_site_name';

    /**
     * @inheritDoc
     */
    public function createExternalSiteMenuItem(int $menuId, ExternalSiteInterface $externalSite): WP_Post
    {
        $title = (string) apply_filters(
            self::FILTER_MENU_EXTERNAL_SITE_NAME,
            $externalSite->languageName(),
            $menuId,
            $externalSite
        );

        $externalSiteUrl = $externalSite->siteUrl();
        $externalSiteId = $externalSite->id();

        $item = get_post(
            wp_update_nav_menu_item(
                $menuId,
                0,
                [
                    'menu-item-title' => esc_attr($title),
                    'menu-item-url' => esc_url($externalSiteUrl),
                ]
            )
        );

        if (!$item instanceof WP_Post) {
            throw new RuntimeException("Couldn't create menu item entry in database");
        }

        $item->object = self::ITEM_TYPE;
        $item->url = esc_url($externalSiteUrl);
        if (empty($item->classes) || !is_array($item->classes)) {
            $item->classes = [];
        }
        $item->classes = array_filter($item->classes);
        $item->classes[] = "site-id-{$externalSiteId}";
        $item->classes[] = 'mlp-external-site-nav-item';
        $item->xfn = 'alternate';

        update_post_meta($item->ID, self::META_KEY_EXTERNAL_SITE_ID, $externalSiteId);
        update_post_meta($item->ID, self::META_KEY_ITEM_TYPE, self::ITEM_TYPE);

        return wp_setup_nav_menu_item($item);
    }
}
