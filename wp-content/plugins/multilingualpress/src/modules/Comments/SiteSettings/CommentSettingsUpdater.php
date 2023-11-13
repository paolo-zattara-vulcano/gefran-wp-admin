<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\Comments\SiteSettings;

use Inpsyde\MultilingualPress\Framework\Http\Request;
use Inpsyde\MultilingualPress\Framework\Setting\SiteSettingsUpdatable;
use Inpsyde\MultilingualPress\Module\Comments\CommentsCopy\CommentsCopierInterface;
use RuntimeException;

/**
 * @psalm-type PostTypeName = string
 * @psalm-type OptionName = string
 * @psalm-type siteIds = list<int>
 * @psalm-type CommentSettings = array<PostTypeName, array<OptionName, siteIds>>
 */
class CommentSettingsUpdater implements SiteSettingsUpdatable
{
    public const ACTION_AFTER_COMMENT_SITE_SETTINGS_ARE_UPDATED = 'multilingualpress.after_comment_settings_are_updated';

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var CommentsCopierInterface
     */
    protected $commentsCopier;

    /**
     * @var CommentsSettingsRepositoryInterface
     */
    protected $commentsSettingsRepository;

    public function __construct(
        Request $request,
        CommentsCopierInterface $commentsCopier,
        CommentsSettingsRepositoryInterface $commentsSettingsRepository
    ) {

        $this->request = $request;
        $this->commentsCopier = $commentsCopier;
        $this->commentsSettingsRepository = $commentsSettingsRepository;
    }

    /**
     * @inheritdoc
     */
    public function defineInitialSettings(int $siteId)
    {
        if ($siteId < 1) {
            return;
        }

        $this->updateCommentSettings([], $siteId);
    }

    /**
     * @inheritdoc
     */
    public function updateSettings(int $siteId)
    {
        $commentsSettings = (array)$this->request->bodyValue(
            CommentsSettingsRepository::COMMENTS_TAB_SETTING,
            INPUT_POST,
            FILTER_SANITIZE_NUMBER_INT,
            FILTER_FORCE_ARRAY
        );

        $commentsSettingsToSaveName = CommentsSettingsRepository::COMMENTS_TAB_OPTION_COPY_NEW_COMMENT;
        $commentsSettingsToSave = $this->commentSettingOptionValuesToSave($commentsSettings, $commentsSettingsToSaveName);

        $copyCommentsOptionName = CommentsSettingsRepository::COMMENTS_TAB_OPTION_COPY_COMMENTS;
        $copyCommentsOptionValues = $this->commentSettingOptionValuesToSave($commentsSettings, $copyCommentsOptionName);

        try {
            $this->updateCommentSettings($commentsSettingsToSave, $siteId);

            foreach ($copyCommentsOptionValues as $postType => $values) {
                $remoteSiteIds = $values[$copyCommentsOptionName] ?? [];
                if (!$remoteSiteIds) {
                    continue;
                }

                $commentIds = $this->commentsSettingsRepository->postTypeComments($postType, $siteId);
                $this->commentsCopier->copyCommentsToSites($siteId, $commentIds, $remoteSiteIds);
            }

            do_action(self::ACTION_AFTER_COMMENT_SITE_SETTINGS_ARE_UPDATED, $commentsSettings, $siteId);
        } catch (RuntimeException $exception) {
            throw $exception;
        }
    }

    /**
     * Updates the comment settings for the given site.
     *
     * @param array<string, array<string, int[]>> $commentsSettings The map of post type names to comment option values.
     * @psalm-param CommentSettings
     * @param int $siteId The site ID.
     */
    protected function updateCommentSettings(array $commentsSettings, int $siteId): void
    {
        update_blog_option($siteId, CommentsSettingsRepository::COMMENTS_TAB_SETTING, $commentsSettings);
    }

    /**
     * Returns the comment setting option values by given name.
     *
     * @param array<string, array<string, int[]>> $commentsSettings The map of post type names to comment option values.
     * @psalm-param CommentSettings $commentsSettings
     * @param string $optionName The comment setting option name.
     * @return array The map of post type names to comment setting option values.
     * @psalm-return CommentSettings
     */
    protected function commentSettingOptionValuesToSave(array $commentsSettings, string $optionName): array
    {
        $commentsSettingsToSave = [];

        foreach ($commentsSettings as $postType => $commentsSetting) {
            if (empty($commentsSetting[$optionName])) {
                continue;
            }
            $commentsSettingsToSave[$postType][$optionName] = array_map('intval', $commentsSetting[$optionName]);
        }

        return $commentsSettingsToSave;
    }
}
