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

namespace Inpsyde\MultilingualPress\Module\QuickLinks;

use Inpsyde\MultilingualPress\Framework\Nonce\Nonce;
use Inpsyde\MultilingualPress\Module\QuickLinks\Model\Collection;
use Inpsyde\MultilingualPress\Module\QuickLinks\Model\CollectionFactory;
use Inpsyde\MultilingualPress\Module\QuickLinks\Settings\Repository;
use InvalidArgumentException;

use function Inpsyde\MultilingualPress\printNonceField;

/**
 * @psalm-type siteId = int
 * @psalm-type siteName = string
 * @psalm-type relatedSites = array<siteId, siteName>
 */
class QuickLink
{
    const FILTER_NOFOLLOW_ATTRIBUTE = 'multilingualpress.quicklinks_nofollow';
    public const FILTER_RENDER_AS_SELECT = 'multilingualpress.QuickLinks.RenderAsSelect';
    public const FILTER_QUICKLINK_LABEL = 'multilingualpress.QuickLinks.Label';
    public const FILTER_MODEL_COLLECTION = 'multilingualpress.QuickLinks.ModelCollection';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Nonce
     */
    private $nonce;

    /**
     * @var Repository
     */
    private $settingRepository;

    /**
     * @var array<int, string>
     * @psalm-var relatedSites
     */
    protected $relatedSites;

    /**
     * QuickLink constructor.
     * @param CollectionFactory $collectionFactory
     * @param Nonce $nonce
     * @param Repository $settingRepository
     * @param array<int, string> $relatedSites A map of the related site site IDs to site names.
     * @psalm-param relatedSites $relatedSites
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Nonce $nonce,
        Repository $settingRepository,
        array $relatedSites
    ) {

        $this->collectionFactory = $collectionFactory;
        $this->nonce = $nonce;
        $this->settingRepository = $settingRepository;
        $this->relatedSites = $relatedSites;
    }

    /**
     * Filter the Post Content
     *
     * Include the Quick Links in the content output
     *
     * @param string $theContent
     * @return string
     */
    public function filter(string $theContent): string
    {
        $post = get_post();

        if (!$post || !is_singular() || !is_main_query()) {
            return $theContent;
        }

        $position = $this->settingRepository->settingValue(Repository::MODULE_SETTING_QUICKLINKS_POSITION);
        $currentBlogId = get_current_blog_id();

        try {
            $modelCollection = $this->collectionFactory->create($currentBlogId, $post->ID);
            $modelCollection = apply_filters(self::FILTER_MODEL_COLLECTION, $modelCollection);

            $modelCollectionAsMap = iterator_to_array($modelCollection->getIterator());

            if (empty($modelCollectionAsMap)) {
                return $theContent;
            }
        } catch (InvalidArgumentException $exc) {
            return $theContent;
        }

        if (0 === count($modelCollection)) {
            return $theContent;
        }

        ob_start();
        $this->render($position, $modelCollection);
        $render = ob_get_clean();

        $newContent = (strncmp($position, 'bottom', 6) === 0)
            ? $theContent . $render
            : $render . $theContent;

        return $newContent;
    }

    /**
     * Render
     *
     * @param string $position
     * @param Collection $modelCollection
     */
    protected function render(string $position, Collection $modelCollection)
    {
        ?>
        <div class="mlp-quicklinks mlp-quicklinks--<?= sanitize_html_class($position) ?>">
            <?php $this->renderCollection($modelCollection); ?>
        </div>
        <?php
    }

    /**
     * Render the collection.
     *
     * @param Collection $collection
     */
    protected function renderCollection(Collection $collection): void
    {
        apply_filters(self::FILTER_RENDER_AS_SELECT, 4 < count($collection))
            ? $this->renderAsSelect($collection)
            : $this->renderAsLinkList($collection);
    }

    /**
     * Render the Quick Links as a List of Links
     *
     * @param Collection $modelCollection
     */
    protected function renderAsLinkList(Collection $modelCollection)
    {
        /**
         * Filter No Follow Attribute
         *
         * Allow to set the `nofollow` attribute or remove it. Default not applied
         *
         * @params bool $noFollow Default to false
         */
        $noFollow = apply_filters(self::FILTER_NOFOLLOW_ATTRIBUTE, false);

        $rel = 'alternate';
        $noFollow and $rel = "{$rel} nofollow";
        ?>
        <nav class="mlp-quicklinks-list mlp-quicklinks-list--links">
            <h5><?= esc_html_x('Read In:', 'QuickLinks', 'multilingualpress') ?></h5>
            <ul>
                <?php foreach ($modelCollection as $siteId => $model) :
                        $label = apply_filters(self::FILTER_QUICKLINK_LABEL, $model->label(), $siteId);
                    ?>
                    <li class="mlp-quicklinks-list__item">
                        <a class="mlp-quicklinks-link"
                           href="<?= esc_url($model->url()) ?>"
                           lang="<?= esc_attr($model->language()) ?>"
                           hreflang="<?= esc_attr($model->hreflangDisplayCode()) ?>"
                           rel="<?= esc_attr($rel) ?>"
                        >
                            <?= wp_kses_post($label) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <?php
    }

    /**
     * Render the Quick Links as a Select/Dropdown Element
     *
     * @param Collection $modelCollection
     */
    protected function renderAsSelect(Collection $modelCollection)
    {
        ?>
        <form method="post" action="" class="mlp-quicklinks-form">
            <label class="mlp-quicklinks-form__label" for="mlp_quicklinks_redirect_selection">
                <?= esc_html_x('Read In:', 'QuickLinks', 'multilingualpress') ?>
            </label>

            <select id="mlp_quicklinks_redirect_selection"
                    class="mlp-quicklinks-form__select"
                    name="mlp_quicklinks_redirect_selection"
            >
                <option value=""></option>
                <?php foreach ($modelCollection as $siteId => $model) :
                    $label = apply_filters(self::FILTER_QUICKLINK_LABEL, $model->label(), $siteId);
                    ?>
                    <option value="<?= esc_attr($model->url()) ?>">
                        <?= esc_html($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="submit"
                   class="mlp-quicklinks-form__submit"
                   value="<?= esc_attr_x('Redirect', 'QuickLinks', 'multilingualpress') ?>"
            />

            <?php printNonceField($this->nonce) ?>
        </form>
        <?php
    }
}
