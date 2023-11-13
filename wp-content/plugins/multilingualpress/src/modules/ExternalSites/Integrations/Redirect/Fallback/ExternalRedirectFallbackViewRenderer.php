<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\ExternalSites\Integrations\Redirect\Fallback;

use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSite\ExternalSiteInterface;
use Inpsyde\MultilingualPress\Module\Redirect\Settings\Repository;
use Inpsyde\MultilingualPress\Module\Redirect\Settings\ViewRenderer;

class ExternalRedirectFallbackViewRenderer implements ViewRenderer
{
    /**
     * @var array<ExternalSiteInterface>
     */
    protected $externalSites;

    /**
     * @var Repository
     */
    protected $repository;

    public function __construct(array $externalSites, Repository $repository)
    {
        $this->repository = $repository;
        $this->externalSites = $externalSites;
    }

    /**
     * @inheritDoc
     */
    public function title()
    {
        ?>
        <label for="redirect_fallback">
            <strong class="mlp-setting-name">
                <?= esc_html_x(
                    'Redirect Fallback As An External Site',
                    'External Sites Module Settings',
                    'multilingualpress'
                ) ?>
            </strong>
        </label>
        <?php
    }

    /**
     * @inheritDoc
     */
    public function content()
    {
        $externalSites = $this->externalSites;
        if (count($externalSites) < 1) {
            return;
        }

        $prefix = Repository::MODULE_SETTINGS;
        $redirectFallbackIdSettingName = Repository::MODULE_SETTING_FALLBACK_REDIRECT_EXTERNAL_SITE_ID;

        $selectedSiteId = $this->repository->redirectFallbackExternalSiteId();

        ?>
        <select
            id="<?= esc_attr("{$prefix}_{$redirectFallbackIdSettingName}") ?>"
            name="<?= esc_attr("{$prefix}[{$redirectFallbackIdSettingName}]") ?>">
            <?php $this->renderOptionsForSites($externalSites, $selectedSiteId) ?>
        </select>
        <p class="mlp-settings-table__option-description">
            <?= esc_html_x(
                'Choose where to redirect the user when the browser language does not correspond to any available language sites in the network.',
                'External Sites Module Settings',
                'multilingualpress'
            ) ?>
        </p>
        <?php
    }

    /**
     * Renders the options List of external sites that can be selected.
     *
     * @param ExternalSiteInterface[] $externalSites
     * @param int $selected The selected site ID.
     */
    protected function renderOptionsForSites(array $externalSites, int $selected)
    {
        printf(
            '<option value="0">%s</option>',
            esc_html_x('None', 'External Sites Module Settings', 'multilingualpress')
        );

        foreach ($externalSites as $externalSite) {
            if (!$externalSite->isRedirectEnabled()) {
                continue;
            }

            $externalSiteId = $externalSite->id();
            printf(
                '<option value="%1$d"%2$s>%3$s</option>',
                esc_attr($externalSiteId),
                selected($selected, $externalSiteId, false),
                esc_url($externalSite->siteUrl())
            );
        }
    }
}
