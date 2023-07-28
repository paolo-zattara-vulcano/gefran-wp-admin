<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\OriginalTranslationLanguage\Integrations\QuickLinks;

use Inpsyde\MultilingualPress\Module\QuickLinks\Model\ViewModel;

class QuickLinksOriginalLanguageViewModel implements ViewModel
{
    /**
     * @var string
     */
    protected $modelName;

    /**
     * @var string
     */
    protected $quickLinksModuleSettingsName;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var bool
     */
    protected $value;

    public function __construct(
        string $modelName,
        string $quickLinksModuleSettingsName,
        string $description,
        bool $value
    ) {

        $this->modelName = $modelName;
        $this->quickLinksModuleSettingsName = $quickLinksModuleSettingsName;
        $this->description = $description;
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function id(): string
    {
        return $this->modelName;
    }

    /**
     * @inheritDoc
     */
    public function title(): void
    {
        ?>
        <label for="<?= esc_attr($this->originalLanguageSettingId()) ?>">
            <strong class="mlp-setting-name">
                <?= esc_html_x(
                    'Show Original Language',
                    'QuickLinks Module Settings',
                    'multilingualpress'
                ) ?>
            </strong>
        </label>
        <?php
    }

    /**
     * @inheritDoc
     */
    public function render(): void
    {
        $name = "{$this->quickLinksModuleSettingsName}[{$this->id()}]";
        ?>
        <label for="<?= esc_attr($this->originalLanguageSettingId()) ?>">
            <input type="checkbox"
                   id="<?= esc_attr($this->originalLanguageSettingId()) ?>"
                   name="<?= esc_attr($name) ?>"
                   value="1"
                <?= checked(true, $this->value, false) ?>
            />
            <?= esc_html($this->description) ?>
        </label>
        <?php
    }

    /**
     * Returns the original language setting name.
     *
     * @return string
     */
    protected function originalLanguageSettingId(): string
    {
        return "{$this->quickLinksModuleSettingsName}_{$this->id()}";
    }
}
