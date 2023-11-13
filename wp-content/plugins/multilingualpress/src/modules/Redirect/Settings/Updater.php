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

namespace Inpsyde\MultilingualPress\Module\Redirect\Settings;

use Inpsyde\MultilingualPress\Framework\Http\Request;
use Inpsyde\MultilingualPress\Framework\Nonce\Nonce;

/**
 * Class Updater
 * @package Inpsyde\MultilingualPress\Module\Redirect
 */
class Updater
{
    /**
     * @var Nonce
     */
    private $nonce;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * Updater constructor
     *
     * @param Nonce $nonce
     * @param Repository $repository
     */
    public function __construct(Nonce $nonce, Repository $repository)
    {
        $this->nonce = $nonce;
        $this->repository = $repository;
    }

    /**
     * Update Module Redirect Settings
     *
     * @param Request $request
     */
    public function updateSettings(Request $request)
    {
        if (!$this->nonce->isValid()) {
            return;
        }

        $settings = $request->bodyValue(
            Repository::MODULE_SETTINGS,
            INPUT_POST,
            FILTER_DEFAULT,
            FILTER_REQUIRE_ARRAY
        );

        if (!$settings) {
            return;
        }

        $settings = filter_var_array(
            $settings,
            [
                Repository::MODULE_SETTING_FALLBACK_REDIRECT_SITE_ID => FILTER_SANITIZE_NUMBER_INT,
                Repository::MODULE_SETTING_FALLBACK_REDIRECT_EXTERNAL_SITE_ID => FILTER_SANITIZE_NUMBER_INT,
            ]
        );

        $this->repository->updateModuleSettings($settings);
    }
}
