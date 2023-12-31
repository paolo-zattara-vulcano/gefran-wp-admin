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

namespace Inpsyde\MultilingualPress\Core\Admin;

use Inpsyde\MultilingualPress\Framework\Admin\SettingsPageTabData;
use Inpsyde\MultilingualPress\Framework\Admin\SettingsPageView;
use Inpsyde\MultilingualPress\Framework\Http\Request;
use Inpsyde\MultilingualPress\Framework\Nonce\Nonce;
use Inpsyde\MultilingualPress\Framework\Setting\Site\SiteSettingView;

use function Inpsyde\MultilingualPress\printNonceField;
use function Inpsyde\MultilingualPress\settingsPageHead;

/**
 * Class PostTypeSlugsSettingsTabView
 */
final class PostTypeSlugsSettingsTabView implements SettingsPageView
{
    /**
     * @var SettingsPageTabData
     */
    private $data;

    /**
     * @var Nonce
     */
    private $nonce;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var SiteSettingView
     */
    private $view;

    /**
     * @param SettingsPageTabData $data
     * @param SiteSettingView $view
     * @param Request $request
     * @param Nonce $nonce
     */
    public function __construct(
        SettingsPageTabData $data,
        SiteSettingView $view,
        Request $request,
        Nonce $nonce
    ) {

        $this->data = $data;
        $this->view = $view;
        $this->request = $request;
        $this->nonce = $nonce;
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        $siteId = (int)$this->request->bodyValue('id', INPUT_GET, FILTER_SANITIZE_NUMBER_INT);
        if (!$siteId) {
            wp_die(esc_html__('Invalid site ID.', 'multilingualpress'));
        }

        $site = get_site($siteId);
        if (!$site) {
            wp_die(esc_html__('The requested site does not exist.', 'multilingualpress'));
        }
        ?>
        <div class="wrap">
            <?php settingsPageHead($site, $this->data->id()) ?>
            <form
                action="<?= esc_url(admin_url('admin-post.php')) ?>"
                method="post">
                <input
                    type="hidden"
                    name="action"
                    value="<?= esc_attr(PostTypeSlugsSettingsUpdateRequestHandler::ACTION) ?>">
                <input
                    type="hidden"
                    name="id"
                    value="<?= esc_attr((string)$siteId) ?>">
                <?php printNonceField($this->nonce) ?>
                <?php $this->view->render($siteId) ?>
                <?php submit_button() ?>
            </form>
        </div>
        <?php
    }
}
