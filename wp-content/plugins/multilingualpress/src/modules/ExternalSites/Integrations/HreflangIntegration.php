<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\ExternalSites\Integrations;

use Inpsyde\MultilingualPress\Core\Frontend\AlternateLanguages;
use Inpsyde\MultilingualPress\Framework\Integration\Integration;
use Inpsyde\MultilingualPress\Framework\WordpressContext;
use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSite\ExternalSiteInterface;
use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSitesMetaBox\ExternalSitesMetaBoxView;

class HreflangIntegration implements Integration
{
    /**
     * @var array<ExternalSiteInterface>
     */
    protected $externalSites;

    /**
     * @var array
     */
    protected $ksesTags;

    public function __construct(array $externalSites, array $ksesTags)
    {
        $this->externalSites = $externalSites;
        $this->ksesTags = $ksesTags;
    }

    /**
     * @inheritDoc
     * phpcs:disable Inpsyde.CodeQuality.NestingLevel.High
     */
    public function integrate(): void
    {
        // phpcs:enable

        add_filter(AlternateLanguages::FILTER_HREFLANG_TRANSLATIONS, function (array $translations): array {
            foreach ($this->externalSites as $externalSite) {
                if (!$externalSite->isHreflangEnabled()) {
                    continue;
                }

                $remoteContentUrl = $this->externalSiteUrlById($externalSite->id());
                if (!$remoteContentUrl) {
                    continue;
                }

                $translations[$externalSite->locale()] = $remoteContentUrl;
            }

            return $translations;
        });
    }

    /**
     * Gets the external site url from entity meta by given external site ID.
     *
     * @param int $externalSiteId The external site ID.
     * @return string The eternal site url.
     */
    protected function externalSiteUrlById(int $externalSiteId): string
    {
        $context = new WordpressContext();
        $externalSitesMeta = (array)get_post_meta($context->queriedObjectId(), ExternalSitesMetaBoxView::META_NAME, true);

        return $externalSitesMeta[$externalSiteId]['url'] ?? '';
    }
}
