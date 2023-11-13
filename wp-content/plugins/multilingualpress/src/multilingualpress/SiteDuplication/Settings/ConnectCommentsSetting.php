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

namespace Inpsyde\MultilingualPress\SiteDuplication\Settings;

use Inpsyde\MultilingualPress\Framework\Setting\Site\SiteSettingViewModel;
use Inpsyde\MultilingualPress\SiteDuplication\SiteDuplicator;

class ConnectCommentsSetting implements SiteSettingViewModel
{
    /**
     * @var string
     */
    protected $inputId;

    public function __construct(string $inputId)
    {
        $this->inputId = $inputId;
    }

    /**
     * @inheritdoc
     */
    public function render(int $siteId)
    {
        ?>
        <label for="<?= esc_attr($this->inputId) ?>">
            <input
                type="checkbox"
                value="1"
                id="<?= esc_attr($this->inputId) ?>"
                name="<?= esc_attr(SiteDuplicator::NAME_CONNECT_COMMENTS) ?>"
                disabled>
            <?php
            esc_html_e(
                'Connect the comments from source site (the site selected in Based on site option)',
                'multilingualpress'
            )
            ?>
        </label>
        <?php
    }

    /**
     * @inheritdoc
     */
    public function title(): string
    {
        return sprintf(
            '<label for="%2$s">%1$s</label>',
            esc_html__('Connect Comments', 'multilingualpress'),
            esc_attr($this->inputId)
        );
    }
}
