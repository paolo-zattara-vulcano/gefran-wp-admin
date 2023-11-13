<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSite;

class ExternalSite implements ExternalSiteInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $languageName;

    /**
     * @var string
     */
    protected $siteUrl;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var bool
     */
    protected $isRedirectEnabled;

    /**
     * @var bool
     */
    protected $isHreflangEnabled;

    /**
     * @var string
     */
    protected $displayStyle;

    public function __construct(
        int $id,
        string $siteUrl,
        string $languageName,
        string $locale,
        bool $isRedirectEnabled,
        bool $isHreflangEnabled,
        string $displayStyle
    ) {

        $this->id = $id;
        $this->languageName = $languageName;
        $this->siteUrl = $siteUrl;
        $this->locale = $locale;
        $this->isRedirectEnabled = $isRedirectEnabled;
        $this->isHreflangEnabled = $isHreflangEnabled;
        $this->displayStyle = $displayStyle;
    }

    /**
     * @inheritDoc
     */
    public function id(): int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function languageName(): string
    {
        return $this->languageName;
    }

    /**
     * @inheritDoc
     */
    public function siteUrl(): string
    {
        return $this->siteUrl;
    }

    /**
     * @inheritDoc
     */
    public function locale(): string
    {
        return $this->locale;
    }

    /**
     * @inheritDoc
     */
    public function isRedirectEnabled(): bool
    {
        return $this->isRedirectEnabled;
    }

    /**
     * @inheritDoc
     */
    public function isHreflangEnabled(): bool
    {
        return $this->isHreflangEnabled;
    }

    /**
     * @inheritDoc
     */
    public function displayStyle(): string
    {
        return $this->displayStyle;
    }
}
