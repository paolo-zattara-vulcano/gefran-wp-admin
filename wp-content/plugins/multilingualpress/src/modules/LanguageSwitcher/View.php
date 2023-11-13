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

use Inpsyde\MultilingualPress\Language\EmbeddedLanguage;

use function Inpsyde\MultilingualPress\sanitizeHtmlClass;

class View
{
    public const FILTER_ITEM_LANGUAGE_NAME = 'multilingualpress.language_switcher_item_language_name';
    public const FILTER_LANGUAGE_SWITCHER_ITEM_FLAG_URL = 'multilingualpress.languageSwitcher.ItemFlagUrl';
    public const FILTER_LANGUAGE_SWITCHER_ITEMS = 'multilingualpress.languageSwitcher.Items';

    /**
     * Displays widget view in frontend
     *
     * @param array $model
     * @return void
     * phpcs:disable Generic.Metrics.NestingLevel.TooHigh
     */
    public function render(array $model)
    {
        // phpcs:enable

        if (empty($model)) {
            return;
        }

        $beforeTitle = $model['before_title'] ?? '';
        $title = $model['title'] ?? '';
        $afterTitle = $model['after_title'] ?? '';

        $title = $this->title((string)$beforeTitle, (string)$title, (string)$afterTitle);

        if ($title) {
            echo wp_kses_post($title);
        }

        $languageSwitcherItems = apply_filters(self::FILTER_LANGUAGE_SWITCHER_ITEMS, $model['items'], $model);

        if (empty($languageSwitcherItems)) {
            return;
        }

        ?>
            <nav class="mlp-language-switcher-nav" aria-label="<?= esc_attr__('Language menu', 'multilingualpress') ?>">
                <ul>
                    <?php foreach ($languageSwitcherItems as $item) :
                        assert($item instanceof Item);

                        $itemClasses = $this->itemClass($item->siteId());
                        $languageName = (string)apply_filters(self::FILTER_ITEM_LANGUAGE_NAME, $item->languageName());
                        $locale = EmbeddedLanguage::changeLanguageVariantLocale($item->locale());
                        $flagUrl = (string)apply_filters(self::FILTER_LANGUAGE_SWITCHER_ITEM_FLAG_URL, $item->flag(), $item->siteId(), $item->type());
                        ?>
                        <li class="<?= sanitizeHtmlClass($itemClasses) // phpcs:ignore
                        // WordPress.XSS.EscapeOutput.OutputNotEscaped ?>">
                            <a href="<?= esc_url($item->url()) ?>"
                               class="mlp-language-switcher-item__link"
                               lang="<?= esc_attr($locale) ?>"
                               hreflang="<?= esc_attr($item->hreflangDisplayCode()) ?>">
                                <?php if (!empty($model['show_flags']) && $flagUrl) {?>
                                    <img alt="<?= esc_attr($languageName) ?>"
                                         src="<?= esc_url($flagUrl) ?>"
                                    />
                                <?php }?>
                                <?= esc_html($languageName) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        <?php
    }

    /**
     * Creates the widget title markup
     *
     * @param string $beforeTitle
     * @param string $title
     * @param string $afterTitle
     * @return string Tittle markup
     */
    protected function title(string $beforeTitle, string $title, string $afterTitle): string
    {
        if (!$beforeTitle || !$afterTitle) {
            return $title;
        }

        return "{$beforeTitle}{$title}{$afterTitle}";
    }

    /**
     * retrieve an array of item classes
     *
     * @param int $siteId
     * @return array of classes
     */
    protected function itemClass(int $siteId): array
    {
        $currentSiteId = get_current_blog_id();
        $itemClass = ['mlp-language-switcher-item'];

        if ($siteId === $currentSiteId) {
            $itemClass[] = 'mlp-language-switcher-item--current-site';
        }

        return $itemClass;
    }
}
