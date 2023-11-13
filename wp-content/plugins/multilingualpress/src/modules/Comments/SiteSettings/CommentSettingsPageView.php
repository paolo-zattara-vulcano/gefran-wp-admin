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

use Inpsyde\MultilingualPress\Framework\Admin\SettingsPageTabData;
use Inpsyde\MultilingualPress\Framework\Admin\SettingsPageView;
use Inpsyde\MultilingualPress\Framework\Nonce\Nonce;
use Inpsyde\MultilingualPress\Framework\Setting\Site\SiteSettingView;

use function Inpsyde\MultilingualPress\printNonceField;
use function Inpsyde\MultilingualPress\settingsPageHead;

class CommentSettingsPageView implements SettingsPageView
{
    /**
     * @var string
     */
    protected $action;

    /**
     * @var SettingsPageTabData
     */
    protected $data;

    /**
     * @var Nonce
     */
    protected $nonce;

    /**
     * @var int
     */
    protected $siteId;

    /**
     * @var SiteSettingView
     */
    protected $view;

    public function __construct(
        SettingsPageTabData $data,
        SiteSettingView $view,
        int $siteId,
        string $action,
        Nonce $nonce
    ) {

        $this->data = $data;
        $this->view = $view;
        $this->siteId = $siteId;
        $this->nonce = $nonce;
        $this->action = $action;
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        if (!$this->siteId) {
            wp_die(esc_html__('Invalid site ID.', 'multilingualpress'));
        }

        $site = get_site($this->siteId);
        if (!$site) {
            wp_die(esc_html__('The requested site does not exist.', 'multilingualpress'));
        }

        $id = $this->data->id();
        $formId = "{$id}-form";
        ?>
        <div class="wrap">
            <?php settingsPageHead($site, $id) ?>
            <form
                action="<?= esc_url(admin_url('admin-post.php')) ?>"
                method="post"
                id="<?= esc_attr($formId)?>">
                <input
                    type="hidden"
                    name="action"
                    value="<?= esc_attr($this->action) ?>">
                <input
                    type="hidden"
                    name="id"
                    value="<?= esc_attr((string)$this->siteId) ?>">
                <?php printNonceField($this->nonce) ?>
                <?php $this->view->render($this->siteId) ?>
                <?php submit_button() ?>
            </form>
        </div>
        <?php
    }
}
