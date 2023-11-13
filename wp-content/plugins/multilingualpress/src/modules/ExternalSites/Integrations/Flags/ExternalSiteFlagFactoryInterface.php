<?php

namespace Inpsyde\MultilingualPress\Module\ExternalSites\Integrations\Flags;

use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSite\ExternalSiteInterface;

/**
 * Can create flag for external sites.
 */
interface ExternalSiteFlagFactoryInterface
{
    /**
     * Creates flag image tag for given external site.
     *
     * @param ExternalSiteInterface $externalSite
     * @return string The flag(<img>) tag.
     */
    public function createFlagImageTag(ExternalSiteInterface $externalSite): string;

    /**
     * Creates flag image Url for given locale.
     *
     * @param string $externalSiteLocale The language locale.
     * @return string The flag image url.
     */
    public function createFlagUrl(string $externalSiteLocale): string;
}
