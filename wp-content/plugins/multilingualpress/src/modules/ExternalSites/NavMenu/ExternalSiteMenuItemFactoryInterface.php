<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\ExternalSites\NavMenu;

use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSite\ExternalSiteInterface;
use RuntimeException;
use WP_Post;

/**
 * Can create a menu item for external site.
 *
 */
interface ExternalSiteMenuItemFactoryInterface
{
    /**
     * Creates a new menu item in given menu for given external site
     *
     * @param int $menuId The WordPress navigation menu ID.
     * @param ExternalSiteInterface $externalSite The external site.
     * @return WP_Post The WordPress menu item object.
     * @throws RuntimeException If problem creating.
     */
    public function createExternalSiteMenuItem(int $menuId, ExternalSiteInterface $externalSite): WP_Post;
}
