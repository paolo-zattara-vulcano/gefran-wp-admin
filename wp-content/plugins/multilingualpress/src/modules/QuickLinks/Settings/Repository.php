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

namespace Inpsyde\MultilingualPress\Module\QuickLinks\Settings;

/**
 * Class Repository
 * @package Inpsyde\MultilingualPress\Module\QuickLinks
 */
class Repository
{
    public const MODULE_SETTINGS = 'multilingualpress_module_quicklinks_settings';
    public const MODULE_SETTING_QUICKLINKS_POSITION = 'position';

    /**
     * Retrieve the value for the given Quick Links setting name.
     *
     * @param string $settingName The setting name.
     * @return string The setting value.
     */
    public function settingValue(string $settingName): string
    {
        $options = $this->moduleSettings();

        return $options[$settingName] ?? $this->defaultValueForSetting($settingName);
    }

    /**
     * Retrieve the Module Settings
     *
     * @return array
     */
    protected function moduleSettings(): array
    {
        return (array)get_network_option(null, self::MODULE_SETTINGS, []);
    }

    /**
     * Update the Given Module Settings
     *
     * @param array $options
     * @return void
     */
    public function updateModuleSettings(array $options): void
    {
        update_network_option(null, self::MODULE_SETTINGS, $options);
    }

    /**
     * Gets the default setting value by given setting name.
     *
     * @param string $settingName The setting name.
     * @return string The default setting value.
     */
    protected function defaultValueForSetting(string $settingName): string
    {
        switch ($settingName) {
            case self::MODULE_SETTING_QUICKLINKS_POSITION:
                return 'bottom-left';
            default:
                return '';
        }
    }
}
