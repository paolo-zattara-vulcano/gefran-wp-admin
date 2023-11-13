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

use Inpsyde\MultilingualPress\Framework\Api\SiteRelations;
use Inpsyde\MultilingualPress\Framework\Database\Exception\NonexistentTable;
use Inpsyde\MultilingualPress\Framework\Setting\SettingOptionInterface;
use Inpsyde\MultilingualPress\Framework\Setting\Site\SiteSettingViewModel;
use WP_Post_Type;

use function Inpsyde\MultilingualPress\siteNameWithLanguage;

class CommentSettingViewModel implements SiteSettingViewModel
{
    /**
     * @var SiteRelations
     */
    protected $siteRelations;

    /**
     * @var array<SettingOptionInterface>
     */
    protected $options;

    /**
     * @var WP_Post_Type
     */
    protected $postType;

    /**
     * @var CommentsSettingsRepositoryInterface
     */
    protected $siteTabSettingsRepository;

    public function __construct(
        array $options,
        SiteRelations $siteRelations,
        CommentsSettingsRepositoryInterface $siteTabSettingsRepository,
        string $postType
    ) {

        $this->options = $options;
        $this->siteRelations = $siteRelations;
        $this->postType = $postType;
        $this->siteTabSettingsRepository = $siteTabSettingsRepository;
    }

    /**
     * @inheritdoc
     */
    public function render(int $siteId)
    {
        $relatedIds = $this->siteRelations->relatedSiteIds($siteId);
        if (!$relatedIds) {
            ?>
            <p class="description">
                <?php __('This site is not connected to any other site. Please connect this site to other sites to be able to manage comment settings here.', 'multilingualpress'); ?>
            </p>
            <?php
            return;
        }
        ?>
        <p class="description">
            <?php __('You can manage comment settings of this site only to connected sites. Other sites will not show up here.', 'multilingualpress'); ?>
        </p>
        <?php foreach ($this->options as $option) :?>
            <h4 class="mlp-comments"><?= esc_html($option->label()) ?></h4>
            <div class="mlp-relationships-languages">
                <?php
                foreach ($relatedIds as $remoteSiteId) {
                    $this->renderOption($siteId, $remoteSiteId, $option);
                }
                ?>
            </div>
            <?php
        endforeach;
    }

    /**
     * Renders the options for given source site.
     *
     * @param int $sourceSiteId The source site ID.
     * @param int $remoteSiteId The Remote site ID.
     * @param SettingOptionInterface $option The option.
     * @return void
     * @throws NonexistentTable
     */
    protected function renderOption(int $sourceSiteId, int $remoteSiteId, SettingOptionInterface $option): void
    {
        $id = "{$this->postType}_{$option->id()}_{$remoteSiteId}";
        $value = $this->siteTabSettingsRepository->settingOptionValue($option->id(), $this->postType, $sourceSiteId);
        ?>
        <p>
            <label for="<?= esc_attr($id) ?>">
                <input
                    type="checkbox"
                    name="<?= esc_attr($this->fieldName($this->postType, $option->id()));?>[]"
                    value="<?= esc_attr($remoteSiteId) ?>"
                    id="<?= esc_attr($id) ?>"
                    <?php checked(in_array($remoteSiteId, $value, true)) ?>>
                <?= esc_html(siteNameWithLanguage($remoteSiteId)) ?>
            </label>
        </p>
        <?php
    }

    /**
     * @inheritdoc
     */
    public function title(): string
    {
        return sprintf(
            '<label for="%2$s">%1$s</label>',
            esc_html($this->postType),
            esc_attr($this->siteTabSettingsRepository::COMMENTS_TAB_SETTING)
        );
    }

    /**
     * Returns the comment setting option name for given post type.
     *
     * @param string $postType The post type name.
     * @param string $optionId The option ID name.
     * @return string The option name.
     */
    protected function fieldName(string $postType, string $optionId): string
    {
        $commentsTabSettingPrefix = CommentsSettingsRepository::COMMENTS_TAB_SETTING;
        return "{$commentsTabSettingPrefix}[{$postType}][{$optionId}]";
    }
}
