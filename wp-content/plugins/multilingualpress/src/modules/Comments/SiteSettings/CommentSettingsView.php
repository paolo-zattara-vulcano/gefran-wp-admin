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

namespace Inpsyde\MultilingualPress\Module\Comments\SiteSettings;

use Inpsyde\MultilingualPress\Framework\Setting\Site\SiteSettingsSectionViewModel;
use Inpsyde\MultilingualPress\Framework\Setting\Site\SiteSettingView;

class CommentSettingsView implements SiteSettingView
{
    public const ACTION_AFTER = 'multilingualpress.after_site_tab_settings';
    public const ACTION_BEFORE = 'multilingualpress.before_site_tab_settings';

    /**
     * @var SiteSettingsSectionViewModel
     */
    protected $model;

    /**
     * @param SiteSettingsSectionViewModel $model
     */
    public function __construct(SiteSettingsSectionViewModel $model)
    {
        $this->model = $model;
    }

    /**
     * @inheritdoc
     */
    public function render(int $siteId): bool
    {
        echo wp_kses_post($this->model->title());
        ?>
        <table class="form-table section-site-settings">
            <?php

            /**
             * Fires right before the settings are rendered.
             *
             * @param int $siteId
             */
            do_action(self::ACTION_AFTER, $siteId);

            $this->model->renderView($siteId);

            /**
             * Fires right after the settings have been rendered.
             *
             * @param int $siteId
             */
            do_action(self::ACTION_BEFORE, $siteId);
            ?>
        </table>
        <?php
        return true;
    }
}
