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

namespace Inpsyde\MultilingualPress\Module\QuickLinks\Model;

use Inpsyde\MultilingualPress\Core\Admin\SiteSettingsRepository;
use Inpsyde\MultilingualPress\Framework\Api\ContentRelations;
use Inpsyde\MultilingualPress\Framework\Api\Translation;
use Inpsyde\MultilingualPress\Framework\Api\Translations;
use Inpsyde\MultilingualPress\Framework\Api\TranslationSearchArgs;
use Inpsyde\MultilingualPress\Framework\Database\Exception\NonexistentTable;
use Inpsyde\MultilingualPress\Framework\Language\Bcp47Tag;
use Inpsyde\MultilingualPress\Framework\NetworkState;
use Inpsyde\MultilingualPress\Framework\Url\SimpleUrl;
use Inpsyde\MultilingualPress\Framework\WordpressContext;
use Inpsyde\MultilingualPress\Module\Redirect\Settings\Repository;
use InvalidArgumentException;
use UnexpectedValueException;

use function Inpsyde\MultilingualPress\siteLocaleName;

/**
 * Class CollectionFactory
 * @package Inpsyde\MultilingualPress\Module\QuickLinks\Model
 */
class CollectionFactory
{
    /**
     * @var ContentRelations
     */
    private $contentRelations;

    /**
     * @var SiteSettingsRepository
     */
    private $siteSettingsRepository;

    /**
     * @var Translations
     */
    private $translations;

    /**
     * @var Repository
     */
    private $redirectSettingsRepository;

    /**
     * FactoryCollection constructor.
     * @param ContentRelations $contentRelations
     * @param SiteSettingsRepository $siteSettingsRepository
     * @param Translations $translations
     * @param Repository $redirectSettingsRepository
     */
    public function __construct(
        ContentRelations $contentRelations,
        SiteSettingsRepository $siteSettingsRepository,
        Translations $translations
    ) {

        $this->contentRelations = $contentRelations;
        $this->siteSettingsRepository = $siteSettingsRepository;
        $this->translations = $translations;
    }

    /**
     * Create the Model Collection
     *
     * All of the models within the collection are related with the given site and content id.
     *
     * @param int $sourceSiteId
     * @param int $sourceContentId
     * @return Collection
     * @throws InvalidArgumentException
     */
    public function create(int $sourceSiteId, int $sourceContentId): Collection
    {
        $emptyCollection = new Collection([]);

        try {
            $relations = $this->contentRelations->relations(
                $sourceSiteId,
                $sourceContentId,
                'post'
            );
        } catch (NonexistentTable $exc) {
            return $emptyCollection;
        }

        unset($relations[$sourceSiteId]);

        if (!$relations) {
            return $emptyCollection;
        }

        return $this->buildModelCollectionByContentRelations($relations);
    }

    /**
     * Build the Collection of Models by the Given Content Relations
     *
     * Content Relations is an array where the keys are the site id and the value the content id.
     *
     * @param array $contentRelations
     * @return Collection
     * @throws InvalidArgumentException
     */
    protected function buildModelCollectionByContentRelations(array $contentRelations): Collection
    {
        $models = [];
        $networkState = $this->networkState();

        foreach ($contentRelations as $remoteSiteId => $remoteContentId) {
            switch_to_blog($remoteSiteId);
            try {
                $currentModel = $this->singleModel($remoteSiteId, $remoteContentId);
            } catch (NonexistentTable $exc) {
                continue;
            } catch (InvalidArgumentException $exc) {
                continue;
            } catch (UnexpectedValueException $exc) {
                continue;
            }

            $models[$remoteSiteId] = $currentModel;
        }
        $networkState->restore();

        return new Collection($models);
    }

    /**
     * Create the Single Model
     *
     * The returned value is an array like this one
     *
     * ```
     * [
     *     'url' => URL OF THE TARGET POST,
     *     'language' => HTTP LANGUAGE CODE OF THE TARGET SITE
     *     'label' => THE TEXT TO USE AS LABEL FOR THE ITEM
     * ]
     * ```
     *
     * @param int $remoteSiteId
     * @param int $remoteContentId
     * @return ModelInterface
     * @throws InvalidArgumentException
     * @throws NonexistentTable
     */
    protected function singleModel(int $remoteSiteId, int $remoteContentId): ModelInterface
    {
        $translations = $this->translations($remoteContentId);
        $translation = $translations[$remoteSiteId] ?? null;

        if (!$translation instanceof Translation) {
            throw new UnexpectedValueException(
                sprintf('No translations found for entity with ID "%1$s"', $remoteContentId)
            );
        }

        $remoteContentUrl = new SimpleUrl($translation->remoteUrl());
        $language = $this->createBcp47Tag($remoteSiteId);
        $label = siteLocaleName($remoteSiteId);

        return new Model($remoteContentUrl, $language, $label, $this->hreflangDisplayCode($remoteSiteId));
    }

    /**
     * Gets the hreflang display code of the given site.
     *
     * @param int $siteId The site ID.
     * @return string The hreflang display code
     */
    protected function hreflangDisplayCode(int $siteId): string
    {
        $hreflangDisplayType = $this->siteSettingsRepository->hreflangSettingForSite(
            $siteId,
            SiteSettingsRepository::NAME_HREFLANG_DISPLAY_TYPE
        );
        $hreflangDisplayTypeIsCountry = $hreflangDisplayType === 'country';
        $context = new WordpressContext();
        $args = TranslationSearchArgs::forContext($context)
            ->forSiteId(get_current_blog_id())
            ->includeBase();

        $translations = $this->translations->searchTranslations($args);
        if (!isset($translations[$siteId])) {
            return (string) $this->createBcp47Tag($siteId);
        }
        $language = $translations[$siteId]->language();
        return $hreflangDisplayTypeIsCountry ? $language->isoCode() : $language->bcp47tag();
    }

    /**
     * Create a NetworkState Instance
     *
     * Basically a wrapper for a static constructor that's difficult to mock in unit tests.
     *
     * @return NetworkState
     */
    protected function networkState(): NetworkState
    {
        return NetworkState::create();
    }

    /**
     * Get the translations for remote content
     *
     * @param int $remoteContentId
     * @return Translations[]
     */
    protected function translations(int $remoteContentId): array
    {
        $args = TranslationSearchArgs::forContext(new WordpressContext())
            ->forSiteId(get_current_blog_id())
            ->includeBase()
            ->forContentId($remoteContentId);

        return $this->translations->searchTranslations($args);
    }

    /**
     * Creates Bcp47Tag for given site ID.
     *
     * @param int $siteId The site ID.
     * @return Bcp47Tag The Bcp47Tag tag
     */
    protected function createBcp47Tag(int $siteId): Bcp47Tag
    {
        return new Bcp47Tag($this->siteSettingsRepository->siteLanguageTag($siteId));
    }
}
