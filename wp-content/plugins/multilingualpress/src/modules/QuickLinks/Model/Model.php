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

namespace Inpsyde\MultilingualPress\Module\QuickLinks\Model;

use Inpsyde\MultilingualPress\Core\Admin\SiteSettingsRepository;
use Inpsyde\MultilingualPress\Framework\Api\Translations;
use Inpsyde\MultilingualPress\Framework\Language\Bcp47Tag;
use Inpsyde\MultilingualPress\Framework\Url\Url;
use InvalidArgumentException;

/**
 * Class Model
 * @package Inpsyde\MultilingualPress\Module\QuickLinks\Model
 */
class Model implements ModelInterface
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $language;

    /**
     * @var string
     */
    private $label;

    private $hreflangDisplayCode;

    /**
     * Model constructor.
     * @param Url $url
     * @param Bcp47Tag $language
     * @param string $label
     * @param string $hreflangDisplayCode
     * @throws InvalidArgumentException
     */
    public function __construct(Url $url, Bcp47Tag $language, string $label, string $hreflangDisplayCode)
    {
        if ('' === $label) {
            throw new InvalidArgumentException('Label cannot be an empty string.');
        }

        $this->url = $url;
        $this->language = $language;
        $this->label = $label;
        $this->hreflangDisplayCode = $hreflangDisplayCode;
    }

    /**
     * @inheritDoc
     */
    public function url(): Url
    {
        return $this->url;
    }

    /**
     * @inheritDoc
     */
    public function language(): Bcp47Tag
    {
        return $this->language;
    }

    /**
     * @inheritDoc
     */
    public function label(): string
    {
        return $this->label;
    }

    public function hreflangDisplayCode(): string
    {
        return $this->hreflangDisplayCode;
    }
}
