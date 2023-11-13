<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\ExternalSites\Integrations\Flags;

use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSite\ExternalSiteInterface;

class ExternalSiteFlagFactory implements ExternalSiteFlagFactoryInterface
{
    /**
     * @var string
     */
    protected $pluginPath;

    /**
     * @var string
     */
    protected $pluginUrl;

    /**
     * @var string
     */
    protected $pathToFlagsFolder;

    public function __construct(
        string $pluginPath,
        string $pluginUrl,
        string $pathToFlagsFolder
    ) {

        $this->pluginPath = $pluginPath;
        $this->pluginUrl = $pluginUrl;
        $this->pathToFlagsFolder = $pathToFlagsFolder;
    }

    /**
     * @inheritDoc
     */
    public function createFlagImageTag(ExternalSiteInterface $externalSite): string
    {
        $alt = sprintf(
            // translators: %s is the external site Language name.
            __('%s language flag', 'multilingualpress'),
            $externalSite->languageName()
        );

        $flagUrl = $this->createFlagUrl($externalSite->locale());
        $flagImageTag = sprintf(
            '<img src="%1$s" alt="%2$s" />',
            esc_url($flagUrl),
            esc_attr($alt)
        );

        return $flagUrl ? $flagImageTag : '';
    }

    /**
     * @inheritDoc
     */
    public function createFlagUrl(string $externalSiteLocale): string
    {
        $countryName = strstr($externalSiteLocale, '_', true);
        $localeName = str_replace('_', '-', $externalSiteLocale);

        $flagLocaleUrl = "{$this->pluginPath}/{$this->pathToFlagsFolder}/{$localeName}.gif";

        $flagImageNameToUse = file_exists($flagLocaleUrl) ? $localeName : $countryName;

        $flagPath = "{$this->pluginPath}/{$this->pathToFlagsFolder}/{$flagImageNameToUse}.gif";
        $flagUrl = "{$this->pluginUrl}/{$this->pathToFlagsFolder}/{$flagImageNameToUse}.gif";

        return file_exists($flagPath) ? $flagUrl : '';
    }
}
