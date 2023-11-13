<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSite;

/**
 * Represents the ExternalSite.
 */
interface ExternalSiteInterface
{
    /**
     * The ID of the external site.
     *
     * @return int
     */
    public function id(): int;

    /**
     * The external site language name.
     *
     * @return string
     */
    public function languageName(): string;

    /**
     * The external site URL.
     *
     * @return string
     */
    public function siteUrl(): string;

    /**
     * The external site language locale.
     *
     * @return string
     */
    public function locale(): string;

    /**
     * Whether redirect is enabled for external site.
     *
     * @return bool
     */
    public function isRedirectEnabled(): bool;

    /**
     * Whether display of hreflang is enabled for external site.
     *
     * @return bool
     */
    public function isHreflangEnabled(): bool;

    /**
     * The external site display style.
     *
     * @return string
     */
    public function displayStyle(): string;
}
