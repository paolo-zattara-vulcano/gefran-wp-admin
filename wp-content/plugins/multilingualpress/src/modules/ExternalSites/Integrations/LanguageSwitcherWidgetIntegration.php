<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\ExternalSites\Integrations;

use Inpsyde\MultilingualPress\Framework\Integration\Integration;
use Inpsyde\MultilingualPress\Framework\WordpressContext;
use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSite\ExternalSiteInterface;
use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSitesMetaBox\ExternalSitesMetaBoxView;
use Inpsyde\MultilingualPress\Module\ExternalSites\Integrations\Flags\ExternalSiteFlagFactoryInterface;
use Inpsyde\MultilingualPress\Module\LanguageSwitcher\ItemFactory as LanguageSwitcherItemFactory;
use Inpsyde\MultilingualPress\Module\LanguageSwitcher\View as LanguageSwitcherView;
use Inpsyde\MultilingualPress\SiteFlags\Flag\Flag;

class LanguageSwitcherWidgetIntegration implements Integration
{
    /**
     * @var array<ExternalSiteInterface>
     */
    protected $externalSites;

    /**
     * @var LanguageSwitcherItemFactory
     */
    protected $itemFactory;

    /**
     * @var ExternalSiteFlagFactoryInterface
     */
    protected $externalSiteFlagFactory;

    /**
     * @var string
     */
    protected $externalSiteKeyWord;

    /**
     * @var bool
     */
    protected $isSiteFlagsModuleActive;

    public function __construct(
        array $externalSites,
        LanguageSwitcherItemFactory $itemFactory,
        ExternalSiteFlagFactoryInterface $externalSiteFlagFactory,
        string $externalSiteKeyWord,
        bool $isSiteFlagsModuleActive
    ) {

        $this->externalSites = $externalSites;
        $this->itemFactory = $itemFactory;
        $this->externalSiteFlagFactory = $externalSiteFlagFactory;
        $this->externalSiteKeyWord = $externalSiteKeyWord;
        $this->isSiteFlagsModuleActive = $isSiteFlagsModuleActive;
    }

    /**
     * @inheritDoc
     * phpcs:disable Inpsyde.CodeQuality.NestingLevel.High
     */
    public function integrate(): void
    {
        // phpcs:enable

        add_filter(
            LanguageSwitcherView::FILTER_LANGUAGE_SWITCHER_ITEMS,
            function (array $languageSwitcherItems, array $languageSwitcherModel): array {
                if (empty($languageSwitcherModel['show_external_sites'])) {
                    return $languageSwitcherItems;
                }

                foreach ($this->externalSites as $externalSite) {
                    $languageSwitcherItems[] = $this->itemFactory->create(
                        $externalSite->languageName(),
                        $externalSite->locale(),
                        $externalSite->locale(),
                        '',
                        $this->externalSiteUrlById($externalSite->id()),
                        $externalSite->id(),
                        $externalSite->locale(),
                        $this->externalSiteKeyWord
                    );
                }

                return $languageSwitcherItems;
            },
            10,
            2
        );

        add_filter(
            LanguageSwitcherView::FILTER_LANGUAGE_SWITCHER_ITEM_FLAG_URL,
            function (string $flagUrl, int $siteId, string $type): string {
                if ($type !== 'external' || !interface_exists(Flag::class) || !$this->isSiteFlagsModuleActive) {
                    return $flagUrl;
                }

                foreach ($this->externalSites as $externalSite) {
                    if ($siteId !== $externalSite->id()) {
                        continue;
                    }

                    return $this->externalSiteFlagFactory->createFlagUrl($externalSite->locale());
                }

                return $flagUrl;
            },
            100,
            3
        );
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
