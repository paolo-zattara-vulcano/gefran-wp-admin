<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\Comments\SiteSettings;

/**
 * The repository for comment settings.
 *
 * @psalm-type PostTypeName = string
 * @psalm-type OptionName = string
 * @psalm-type siteIds = list<int>
 * @psalm-type CommentSettings = array<PostTypeName, array<OptionName, siteIds>>
 */
interface CommentsSettingsRepositoryInterface
{
    /**
     * Gets the given setting option value for the post type of the site.
     *
     * @param string $optionName The option name.
     * @param string $postTypeName The post type name.
     * @param int $siteId The site ID.
     * @return int[] The list of site IDs.
     */
    public function settingOptionValue(
        string $optionName,
        string $postTypeName,
        int $siteId
    ): array;

    /**
     * Gets all comment IDs of a given site for a given post type.
     *
     * @param string $postType The post type name.
     * @param int $siteId The site ID.
     * @return int[] A list of comment IDs.
     */
    public function postTypeComments(string $postType, int $siteId): array;

    /**
     * Gets the comments settings of a given site.
     *
     * @param int $siteId The site ID.
     * @return array<string, array<string, int[]>> The map of post type names to comment setting option values.
     * @psalm-return CommentSettings
     */
    public function allSettings(int $siteId): array;
}
