<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\ExternalSites\Integrations;

use Inpsyde\MultilingualPress\Framework\Integration\Integration;
use Inpsyde\MultilingualPress\Framework\Language\Bcp47Tag;
use Inpsyde\MultilingualPress\Framework\Url\SimpleUrl;
use Inpsyde\MultilingualPress\Framework\WordpressContext;
use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSite\ExternalSiteInterface;
use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSitesMetaBox\ExternalSitesMetaBoxView;
use Inpsyde\MultilingualPress\Module\QuickLinks\Model\Collection;
use Inpsyde\MultilingualPress\Module\QuickLinks\Model\Model as QuickLinksModel;
use Inpsyde\MultilingualPress\Module\QuickLinks\QuickLink;

class QuickLinksIntegration implements Integration
{
    /**
     * @var array<ExternalSiteInterface>
     */
    protected $externalSites;

    public function __construct(array $externalSites)
    {
        $this->externalSites = $externalSites;
    }

    /**
     * @inheritDoc
     * phpcs:disable Generic.Metrics.NestingLevel.TooHigh
     * phpcs:disable Inpsyde.CodeQuality.NestingLevel.High
     */
    public function integrate(): void
    {
        // phpcs:enable

        add_filter(QuickLink::FILTER_MODEL_COLLECTION, function (Collection $modelCollection): Collection {
            $externalSitesModels = [];
            foreach ($this->externalSites as $externalSite) {
                $remoteContentUrl = $this->externalSiteUrlById($externalSite->id());
                if (empty($remoteContentUrl)) {
                    continue;
                }

                $remoteContentUrl = new SimpleUrl($remoteContentUrl);
                $language = new Bcp47Tag($externalSite->locale());
                $label = $externalSite->languageName();

                $externalSitesModels[] = new QuickLinksModel($remoteContentUrl, $language, $label, (string) $language);
            }

            return new Collection(array_merge($modelCollection->getIterator()->getArrayCopy(), $externalSitesModels));
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
