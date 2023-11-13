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

class CommentsSettingsRepository implements CommentsSettingsRepositoryInterface
{
    public const COMMENTS_TAB_UPDATE_ACTION_NAME = 'update_multilingualpress_comments_site_settings';
    public const COMMENTS_TAB_NONCE_NAME = 'save_site_comment_settings';
    public const COMMENTS_TAB_SETTING = 'mlp_site_comments';
    public const COMMENTS_TAB_OPTION_COPY_COMMENTS = 'comments_copy';
    public const COMMENTS_TAB_OPTION_COPY_NEW_COMMENT = 'copy_new_comment';
    public const FILTER_COMMENTS_ENABLED_FOR_POST_TYPE = 'multilingualpress.are_comments_enabled_for_post_type';

    /**
     * @inheritDoc
     */
    public function settingOptionValue(string $optionName, string $postTypeName, int $siteId): array
    {
        $savedSettings = $this->allSettings($siteId);

        return $savedSettings[$postTypeName][$optionName] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function postTypeComments(string $postType, int $siteId): array
    {
        switch_to_blog($siteId);

        $args = [
            'fields' => 'ids',
            'post_type' => $postType,
        ];
        $comments = get_comments($args);

        restore_current_blog();

        return $comments;
    }

    /**
     * @inheritDoc
     */
    public function allSettings(int $siteId): array
    {
        return (array)get_blog_option($siteId, self::COMMENTS_TAB_SETTING, []);
    }
}
