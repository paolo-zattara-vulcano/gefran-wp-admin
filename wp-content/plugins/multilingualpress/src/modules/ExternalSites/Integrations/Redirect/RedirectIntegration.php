<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\ExternalSites\Integrations\Redirect;

use Inpsyde\MultilingualPress\Database\Table\ExternalSitesTable;
use Inpsyde\MultilingualPress\Framework\Integration\Integration;
use Inpsyde\MultilingualPress\Framework\WordpressContext;
use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSite\ExternalSiteInterface;
use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSitesRepository\ExternalSitesRepositoryInterface;
use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSitesMetaBox\ExternalSitesMetaBoxView;
use Inpsyde\MultilingualPress\Module\Redirect\LanguageNegotiator;
use Inpsyde\MultilingualPress\Module\Redirect\Redirector;
use Inpsyde\MultilingualPress\Module\Redirect\Settings\Repository;
use Inpsyde\MultilingualPress\Module\Redirect\Settings\TabView;
use Inpsyde\MultilingualPress\Module\Redirect\Settings\ViewRenderer;

use function Inpsyde\MultilingualPress\callExit;

class RedirectIntegration implements Integration
{
    /**
     * @var array<ExternalSiteInterface>
     */
    protected $externalSites;

    /**
     * @var LanguageNegotiator
     */
    protected $languageNegotiator;

    /**
     * @var ExternalSiteRedirectTargetFactoryInterface
     */
    protected $externalSiteRedirectTargetFactory;

    /**
     * @var Repository
     */
    protected $redirectSettingsRepository;

    /**
     * @var ViewRenderer
     */
    protected $externalRedirectFallbackViewRenderer;

    /**
     * @var ExternalSitesRepositoryInterface
     */
    protected $externalSitesRepository;

    public function __construct(
        array $externalSites,
        LanguageNegotiator $languageNegotiator,
        ExternalSiteRedirectTargetFactoryInterface $externalSiteRedirectTargetFactory,
        Repository $redirectSettingsRepository,
        ViewRenderer $externalRedirectFallbackViewRenderer,
        ExternalSitesRepositoryInterface $externalSitesRepository
    ) {

        $this->externalSites = $externalSites;
        $this->languageNegotiator = $languageNegotiator;
        $this->externalSiteRedirectTargetFactory = $externalSiteRedirectTargetFactory;
        $this->redirectSettingsRepository = $redirectSettingsRepository;
        $this->externalRedirectFallbackViewRenderer = $externalRedirectFallbackViewRenderer;
        $this->externalSitesRepository = $externalSitesRepository;
    }

    /**
     * @inheritDoc
     * phpcs:disable Inpsyde.CodeQuality.NestingLevel.High
     */
    public function integrate(): void
    {
        // phpcs:enable

        if (! $this->isRedirectEnabledForAnyExternalSite()) {
            return;
        }

        add_filter(
            LanguageNegotiator::FILTER_REDIRECT_TARGETS,
            function (array $targets): array {
                foreach ($this->externalSites as $externalSite) {
                    if (!$externalSite->isRedirectEnabled()) {
                        continue;
                    }

                    $languageTag = strtolower($externalSite->locale());
                    $languageTag = str_replace('_', '-', $languageTag);

                    $languageName = str_replace('_', '-', $externalSite->locale());

                    $targets[] = $this->externalSiteRedirectTargetFactory->createExternalSiteRedirectTarget(
                        [
                            'locale' => $languageName,
                            'priority' => 1,
                            'siteId' => $externalSite->id(),
                            'url' => $this->externalSiteUrlById($externalSite->id()) ?: $externalSite->siteUrl(),
                            'user_priority' => $this->languageNegotiator->languagePriority($languageTag),
                        ]
                    );
                }

                return $targets;
            }
        );

        $this->integrateRedirectFallback();
    }

    /**
     * Integrates the redirect fallback functionality for external sites.
     *
     * @return void
     */
    protected function integrateRedirectFallback(): void
    {
        add_filter(TabView::FILTER_VIEW_MODELS, function (array $renderers) {
            $renderers[] = $this->externalRedirectFallbackViewRenderer;
            return $renderers;
        });

        add_action(Redirector::ACTION_TARGET_NOT_FOUND, function () {
            $redirectFallbackSiteId = $this->redirectSettingsRepository->redirectFallbackSiteId();
            $redirectFallbackExternalSiteId = $this->redirectSettingsRepository->redirectFallbackExternalSiteId();

            if ($redirectFallbackSiteId > 0 || $redirectFallbackExternalSiteId < 1) {
                return;
            }

            $externalSite = $this->externalSitesRepository->externalSiteBy(ExternalSitesTable::COLUMN_ID, $redirectFallbackExternalSiteId);

            //phpcs:disable WordPressVIPMinimum.Security.ExitAfterRedirect.NoExit
            //phpcs:disable WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
            wp_redirect($externalSite->siteUrl());
            callExit();
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

    /**
     * Checks if redirect is enabled for any external site.
     *
     * @return bool true if redirect is enabled for any external site, otherwise false.
     */
    protected function isRedirectEnabledForAnyExternalSite(): bool
    {
        foreach ($this->externalSites as $externalSite) {
            if ($externalSite->isRedirectEnabled()) {
                return true;
            }
        }

        return false;
    }
}
