<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\ExternalSites\Settings;

use Inpsyde\MultilingualPress\Database\Table\ExternalSitesTable;
use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSite\ExternalSiteInterface;
use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSitesRepository\ExternalSitesRepositoryInterface;

use function Inpsyde\MultilingualPress\arrayToAttrs;

/**
 * @psalm-type Attributes = array{
 *      class?: string,
 *      size?: int,
 *      data-connected?: string,
 *      data-none?: string
 * }
 * @psalm-type Column = array{
 *      header: string,
 *      type: string,
 *      attributes: Attributes,
 *      options: array<string, string>
 * }
 * @psalm-type ColumnName = string
 */
class TableFormView
{
    /**
     * @var string
     */
    public const INPUT_NAME_PREFIX = 'externalSites';

    /**
     * @var string
     */
    public const TABLE_ID = 'mlp-external-sites-table';

    /**
     * @var ExternalSitesRepositoryInterface
     */
    protected $externalSitesRepository;

    /**
     * @var array
     */
    protected $columns;

    /**
     * @param ExternalSitesRepositoryInterface $externalSitesRepository
     * @param array $columns
     * @psalm-param array<ColumnName, Column>
     */
    public function __construct(ExternalSitesRepositoryInterface $externalSitesRepository, array $columns)
    {
        $this->externalSitesRepository = $externalSitesRepository;
        $this->columns = $columns;
    }

    /**
     * Renders the table.
     *
     * @return void
     */
    public function render(): void
    {
        ?>
        <table id="<?php echo esc_attr(self::TABLE_ID); ?>" class="widefat <?php echo esc_attr(self::TABLE_ID); ?>">
            <thead>
                <tr><?php $this->header(); ?></tr>
            </thead>
            <tbody><?php $this->tBody(); ?></tbody>
            <tfoot>
                <tr><?php $this->header(); ?></tr>
            </tfoot>
        </table>
        <?php
    }

    /**
     * The table body markup.
     *
     * @return void
     */
    protected function tBody(): void
    {
        $rows = $this->externalSitesRepository->allExternalSites();

        if (!$rows) {
            $this->emptyRow();
            return;
        }

        foreach ($rows as $row) {
            $this->row($row->id(), $row);
        }

        $this->emptyRow();
    }

    /**
     * Creates an empty row.
     *
     * @return void
     */
    protected function emptyRow(): void
    {
        ?>
        <tr>
            <?php
            foreach ($this->columns as $col => $data) {
                $this->column($col, $this->externalSitesRepository->autoIncrementValue(), $data, $data->$col ?? '');
            }
            ?>
        </tr>
        <?php
    }

    /**
     * The row HTML markup.
     *
     * @param int $id The row ID.
     * @param ExternalSiteInterface $externalSite
     * @return void
     */
    protected function row(int $id, ExternalSiteInterface $externalSite): void
    {
        $cols = [
            ExternalSitesTable::COLUMN_SITE_LANGUAGE_NAME => $externalSite->languageName(),
            ExternalSitesTable::COLUMN_SITE_URL => $externalSite->siteUrl(),
            ExternalSitesTable::COLUMN_SITE_LANGUAGE_LOCALE => $externalSite->locale(),
            ExternalSitesTable::COLUMN_REDIRECT => $externalSite->isRedirectEnabled(),
            ExternalSitesTable::COLUMN_ENABLE_HREFLANG => $externalSite->isHreflangEnabled(),
            ExternalSitesTable::COLUMN_DISPLAY_STYLE => $externalSite->displayStyle(),
        ];
        ?>
        <tr>
            <?php
            foreach ($this->columns as $col => $data) {
                if (!array_key_exists($col, $cols)) {
                    continue;
                }

                $this->column($col, $id, $data, $cols[$col]);
            }
            ?>
        </tr>
        <?php
    }

    /**
     * The column HTML markup.
     *
     * @param string $col The column Name.
     * @param int $id The row ID.
     * @param array $data The column configuration data.
     * @psalm-param Column $data
     * @param scalar $value The input value.
     * @return void
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
     */
    protected function column(string $col, int $id, array $data, $value): void
    {
        // phpcs:enable
        ?>
        <td data-label="<?= esc_attr($data['header'] ?? '') ?>">
            <?php
            $attrs = $data['attributes'] ?? [];
            $inputType = $data['type'] ?? '';
            $func = [$this, $inputType];

            if ($inputType === 'checkbox') {
                $value = (bool)$value;
            }

            if ($inputType === 'select') {
                $func($id, $col, $value, $data['options'] ?? [], $attrs);
                return;
            }

            if (!is_callable($func)) {
                echo wp_kses_post($value);
                return;
            }

            $func($id, $col, $value, $attrs);
            ?>
        </td>
        <?php
    }

    /**
     * The input markup.
     *
     * @param int $id The row ID.
     * @param string $col The column name.
     * @param string $value The input value.
     * @param array $attributes The column attributes configuration.
     * @psalm-param Attributes $attributes
     * @return void
     */
    protected function text(int $id, string $col, string $value, array $attributes = []): void
    {
        ?>
        <input type="text"
               name="<?= esc_attr($this->inputName($id, $col)); ?>"
               id="<?= esc_attr($this->inputId($id, $col)) ?>"
               value="<?= esc_attr($value); ?>"
            <?= esc_attr(arrayToAttrs($attributes)); ?>
        />
        <?php
    }

    /**
     * The checkbox markup.
     *
     * @param int $id The row ID.
     * @param string $col The column name.
     * @param bool $value The input value.
     * @param array $attributes The column attributes configuration.
     * @psalm-param Attributes $attributes
     */
    protected function checkbox(int $id, string $col, bool $value, array $attributes = []): void
    {
        ?>
        <input type="checkbox"
               name="<?= esc_attr($this->inputName($id, $col)); ?>"
               id="<?= esc_attr($this->inputId($id, $col)) ?>"
               value="1"
            <?= checked(1, $value, false); ?>
            <?= esc_attr(arrayToAttrs($attributes)); ?>
        />
        <?php
    }

    /**
     * The select markup.
     *
     * @param int $id The row ID.
     * @param string $col The column name.
     * @param string $value The input value.
     * @param array<string, string> $options The select options.
     * @param array $attributes The column attributes configuration.
     * @psalm-param Attributes $attributes
     */
    protected function select(int $id, string $col, string $value, array $options, array $attributes = []): void
    {
        ?>
        <select
            id="<?= esc_attr($this->inputId($id, $col)) ?>"
            name="<?= esc_attr($this->inputName($id, $col)); ?>"
            <?= esc_attr(arrayToAttrs($attributes)); ?>
        >
            <?php foreach ($options as $optionKey => $option) : ?>
                <option value="<?= esc_attr($optionKey); ?>" <?= selected($optionKey, $value) ?>><?= esc_html($option); ?></option>
            <?php endforeach;?>
        </select>
        <?php
    }

    /**
     * Creates the input name from given row ID and column name.
     *
     * @param int $id The row ID
     * @param string $col The column name.
     * @return string The input name.
     */
    protected function inputName(int $id, string $col): string
    {
        $inputNamePrefix = self::INPUT_NAME_PREFIX;
        return "{$inputNamePrefix}[{$id}][{$col}]";
    }

    /**
     * Creates the input ID from given row ID and column name.
     *
     * @param int $id The row ID
     * @param string $col The column name.
     * @return string The input ID.
     */
    protected function inputId(int $id, string $col): string
    {
        $inputNamePrefix = self::INPUT_NAME_PREFIX;
        return "{$inputNamePrefix}-{$id}-{$col}";
    }

    /**
     * The table head HTML markup.
     *
     * @return void
     */
    protected function header(): void
    {
        foreach ($this->columns as $data) {
            $data['header'] = $data['header'] ?? '';
            if (!$data['header']) {
                return;
            }
            ?>
            <th scope="col" data-label="<?= esc_attr($data['header'] ?? '') ?>">
                <?= esc_html($data['header']) ?>
            </th>
            <?php
        }
    }
}
