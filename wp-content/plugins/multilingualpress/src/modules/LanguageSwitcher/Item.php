<?php

# -*- coding: utf-8 -*-
/*
 * This file is part of the MultilingualPress package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\LanguageSwitcher;

class Item
{
    /**
     * @var string
     */
    private $languageName;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $isoCode;

    /**
     * @var string
     */
    private $flag;

    /**
     * @var string
     */
    private $url;

    /**
     * @var int
     */
    private $siteId;

    /**
     * @var string
     */
    private $hreflangDisplayCode;

    /**
     * @var string
     */
    protected $type;

    public function __construct(
        string $languageName,
        string $locale,
        string $isoCode,
        string $flag,
        string $url,
        int $siteId,
        string $hreflangDisplayCode,
        string $type = ''
    ) {

        $this->languageName = $languageName;
        $this->locale = $locale;
        $this->isoCode = $isoCode;
        $this->flag = $flag;
        $this->url = $url;
        $this->siteId = $siteId;
        $this->hreflangDisplayCode = $hreflangDisplayCode;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function languageName(): string
    {
        return $this->languageName;
    }

    /**
     * @return string
     */
    public function isoCode(): string
    {
        return $this->isoCode;
    }

    /**
     * @return string
     */
    public function flag(): string
    {
        return $this->flag;
    }

    /**
     * @return string
     */
    public function url(): string
    {
        return $this->url;
    }

    /**
     * @return int
     */
    public function siteId(): int
    {
        return $this->siteId;
    }

    /**
     * @return string
     */
    public function locale(): string
    {
        return $this->locale;
    }

    /**
     * @return string
     */
    public function hreflangDisplayCode(): string
    {
        return $this->hreflangDisplayCode;
    }

    /**
     * The item type.
     *
     * Can be used to specify the special item types like for external sites.
     *
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }
}
