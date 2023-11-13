<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\ExternalSites\NavMenu;

use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSite\ExternalSiteInterface;

class MetaBoxView
{
    public const ID = 'mlp-navMenu-external-sites';

    /**
     * @var ExternalSiteInterface[]
     */
    protected $externalSites;

    /**
     * @var string
     */
    protected $selectAllUrl;

    /**
     * @var array
     */
    protected $submitButtonAttributes;

    public function __construct(array $externalSites, string $selectAllUrl, array $submitButtonAttributes)
    {
        $this->externalSites = $externalSites;
        $this->selectAllUrl = $selectAllUrl;
        $this->submitButtonAttributes = $submitButtonAttributes;
    }

    /**
     * @inheritDoc
     */
    public function render(): void
    {
        ?>
        <div id="<?= esc_attr(self::ID) ?>-container">
            <?php $this->renderCheckboxes() ?>
            <?php $this->renderButtonControls() ?>
        </div>
        <?php
    }

    /**
     * Renders checkboxes to select external sites.
     */
    protected function renderCheckboxes(): void
    {
        if (empty($this->externalSites)) {
            esc_html_e('No items.', 'multilingualpress');

            return;
        }
        ?>
        <div
            id="tabs-panel-<?= esc_attr(self::ID) ?>"
            class="tabs-panel tabs-panel-active">
            <ul id="<?= esc_attr(self::ID) ?>" class="form-no-clear">
                <?php
                foreach ($this->externalSites as $externalSite) {
                    $this->renderCheckbox($externalSite->languageName(), $externalSite->id());
                }
                ?>
            </ul>
        </div>
        <?php
    }

    /**
     * Renders a single item for given external site with given name.
     *
     * @param string $name The item name.
     * @param int $siteId The external site ID.
     */
    protected function renderCheckbox(string $name, int $siteId): void
    {
        ?>
        <li>
            <label class="menu-item-title">
                <input
                    type="checkbox"
                    value="<?= esc_attr((string)$siteId) ?>"
                    class="menu-item-checkbox"
                    data-type="external"
                >
                <?= esc_html($name) ?>
            </label>
        </li>
        <?php
    }

    /**
     * Renders the button controls HTML.
     */
    protected function renderButtonControls(): void
    {
        ?>
        <p class="button-controls wp-clearfix">
            <span class="list-controls">
                <a
                    id="<?= esc_attr(self::ID) ?>-select-all"
                    href="<?= esc_url($this->selectAllUrl) ?>"
                    class="aria-button-if-js">
                    <?= esc_html__('Select All', 'multilingualpress') ?>
                </a>
            </span>
            <span class="add-to-menu">
                <?php
                submit_button(
                    __('Add to Menu', 'multilingualpress'),
                    'button-secondary submit-add-to-menu right',
                    'add-mlp-external-site-item',
                    false,
                    $this->submitButtonAttributes
                );
                ?>
                <span class="spinner"></span>
            </span>
        </p>
        <?php
    }
}
