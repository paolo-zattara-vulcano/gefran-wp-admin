<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSite;

class ExternalSiteFactory implements ExternalSiteFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createExternalSite(array $config): ExternalSiteInterface
    {
        return new ExternalSite(
            (int)$config['ID'],
            $config['site_url'],
            $config['site_language_name'],
            $config['site_language_locale'],
            (bool)$config['site_redirect'],
            (bool)$config['enable_hreflang'],
            $config['display_style']
        );
    }
}
