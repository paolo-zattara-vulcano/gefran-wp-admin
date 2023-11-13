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

namespace Inpsyde\MultilingualPress\Module\User;

use Inpsyde\MultilingualPress\Module\User\TranslationUi\MetaboxAction;

class MetaValueFilter
{
    /**
     * Filter the frontend values for user meta fields and replace with correct translations
     *
     * @param string $authorMeta The value of the metadata.
     * @param mixed $userId The user ID.
     * @return string user meta value replaced with correct translation
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
     */
    public function filterMetaValues(string $authorMeta, $userId): string
    {
        // phpcs:enable

        if (!$userId) {
            return $authorMeta;
        }
        $siteId = get_current_blog_id();
        $userTranslationMeta = get_user_meta($userId, MetaboxAction::TRANSLATION_META, true);
        if (empty($userTranslationMeta)) {
            return $authorMeta;
        }

        $userTranslationMetaForSite = $userTranslationMeta["site-{$siteId}"] ?? [];
        if (empty($userTranslationMetaForSite)) {
            return $authorMeta;
        }

        $field = str_replace(['get_the_author_', 'the_author_'], '', current_filter());

        if (array_key_exists($field, $userTranslationMetaForSite)) {
            if (empty($userTranslationMetaForSite[$field])) {
                return $authorMeta;
            }
            $authorMeta = $userTranslationMetaForSite[$field];
        }
        return $authorMeta;
    }
}
