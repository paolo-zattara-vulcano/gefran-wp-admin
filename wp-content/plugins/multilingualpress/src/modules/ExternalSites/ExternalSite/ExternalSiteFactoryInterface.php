<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSite;

use RuntimeException;

/**
 * Can create an ExternalSite.
 *
 * @psalm-type externalSiteConfig = array{
 *      ID: int,
 *      site_url: string,
 *      site_language_name: string,
 *      site_language_locale: string,
 *      site_redirect: int,
 *      enable_hreflang: int,
 *      display_style: string,
 * }
 */
interface ExternalSiteFactoryInterface
{
    /**
     * Creates a new external site instance with a given config.
     *
     * @param array $config The config.
     * @psalm-param externalSiteConfig $config
     * @return ExternalSiteInterface The new instance.
     * @throws RuntimeException If problem creating.
     */
    public function createExternalSite(array $config): ExternalSiteInterface;
}
