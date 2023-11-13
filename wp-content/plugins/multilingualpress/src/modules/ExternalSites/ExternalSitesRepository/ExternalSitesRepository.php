<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSitesRepository;

use Inpsyde\MultilingualPress\Database\Table\ExternalSitesTable;
use Inpsyde\MultilingualPress\Framework\Database\Exception\NonexistentTable;
use Inpsyde\MultilingualPress\Framework\Database\Table;
use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSite\ExternalSiteFactoryInterface;
use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSite\ExternalSiteInterface;
use RuntimeException;
use wpdb;

/**
 * @psalm-type columnName = string
 * @psalm-type specification = string
 * @psalm-type externalSiteConfig = array{
 *      ID: int,
 *      site_url: string,
 *      site_language_name: string,
 *      site_language_locale: string,
 *      enable_hreflang: int,
 *      site_redirect: int,
 *      display_style: string
 * }
 */
class ExternalSitesRepository implements ExternalSitesRepositoryInterface
{
    /**
     * @var wpdb
     */
    protected $wpdb;

    /**
     * @var Table
     */
    protected $table;

    /**
     * @var ExternalSiteFactoryInterface
     */
    protected $externalSiteFactory;

    /**
     * @var string[]
     */
    protected $requiredColumnNames;

    public function __construct(wpdb $wpdb, Table $table, ExternalSiteFactoryInterface $externalSiteFactory, array $requiredColumnNames)
    {
        $this->wpdb = $wpdb;
        $this->table = $table;
        $this->externalSiteFactory = $externalSiteFactory;
        $this->requiredColumnNames = $requiredColumnNames;
    }

    /**
     * @inheritDoc
     * @throws NonexistentTable
     */
    public function deleteExternalSite(int $id): void
    {
        if (!$this->table->exists()) {
            throw new NonexistentTable(__FUNCTION__, $this->table->name());
        }

        $dbDelete = $this->wpdb->delete(
            $this->table->name(),
            [ExternalSitesTable::COLUMN_ID => $id],
            '%d'
        );

        if (!$dbDelete) {
            throw new RuntimeException("Couldn't delete the external site");
        }
    }

    /**
     * @inheritDoc
     * @throws NonexistentTable
     */
    public function allExternalSites(): array
    {
        if (!$this->table->exists()) {
            throw new NonexistentTable(__FUNCTION__, $this->table->name());
        }

        //phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
        $orderCol = ExternalSitesTable::COLUMN_ID;
        $query = "SELECT * FROM {$this->table->name()} ORDER BY {$orderCol} ASC";
        $results = $this->wpdb->get_results($query, ARRAY_A);
        // phpcs:enable
        if (!$results || !is_array($results)) {
            return [];
        }

        return array_map([$this, 'createExternalSite'], $results);
    }

    /**
     * @inheritDoc
     * @throws NonexistentTable
     */
    public function insertExternalSite(array $externalSiteData): void
    {
        if (!$this->table->exists()) {
            throw new NonexistentTable(__FUNCTION__, $this->table->name());
        }

        $this->validateData($externalSiteData);

        $externalSitesTableColumnSpecifications = $this->extractColumnSpecifications($this->table);
        $dbUpdate = $this->wpdb->insert(
            $this->table->name(),
            $externalSiteData,
            $this->findSpecifications($externalSitesTableColumnSpecifications, $externalSiteData)
        );

        if (!$dbUpdate) {
            throw new runtimeException("Couldn't insert the external site");
        }
    }

    /**
     * @inheritDoc
     * @throws NonexistentTable
     */
    public function updateExternalSite(int $siteId, array $externalSiteData): void
    {
        if (!$this->table->exists()) {
            throw new NonexistentTable(__FUNCTION__, $this->table->name());
        }

        $this->validateData($externalSiteData);

        $externalSitesTableColumnSpecifications = $this->extractColumnSpecifications($this->table);
        $dbUpdate = $this->wpdb->update(
            $this->table->name(),
            $externalSiteData,
            [ExternalSitesTable::COLUMN_ID => $siteId],
            $this->findSpecifications($externalSitesTableColumnSpecifications, $externalSiteData),
            ['%d']
        );

        if ($dbUpdate === false) {
            throw new runtimeException("Couldn't update the external site");
        }
    }

    /**
     * @inheritDoc
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     */
    public function externalSiteBy(string $column, $value): ?ExternalSiteInterface
    {
        if (!$this->table->exists()) {
            throw new NonexistentTable(__FUNCTION__, $this->table->name());
        }
        // phpcs:enable
        $queryableColumns = [
            ExternalSitesTable::COLUMN_ID,
            ExternalSitesTable::COLUMN_SITE_LANGUAGE_NAME,
            ExternalSitesTable::COLUMN_SITE_LANGUAGE_LOCALE,
            ExternalSitesTable::COLUMN_SITE_URL,
            ExternalSitesTable::COLUMN_REDIRECT,
        ];

        if (!in_array($column, $queryableColumns, true)) {
            throw new runtimeException("Wrong column name");
        }

        //phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
        //phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $externalSite = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table->name()} WHERE {$column} = %s LIMIT 1",
                $value
            ),
            ARRAY_A
        );
        // phpcs:enable

        if (!$externalSite || !is_array($externalSite)) {
            return null;
        }

        return $this->createExternalSite($externalSite);
    }

    /**
     * @inheritDoc
     */
    public function autoIncrementValue(): int
    {
        $autoIncrementColumn = ExternalSitesTable::COLUMN_ID;
        $query = "SELECT MAX(`{$autoIncrementColumn}`) AS max_value FROM `{$this->table->name()}`";

        //phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
        return (int)$this->wpdb->get_var($query) + 1;
        // phpcs:enable
    }

    /**
     * Creates a new external site instance with a given config.
     *
     * @param array $config A map of external site field name to value.
     * @psalm-param externalSiteConfig $config
     * @return ExternalSiteInterface The new instance.
     */
    protected function createExternalSite(array $config): ExternalSiteInterface
    {
        return $this->externalSiteFactory->createExternalSite($config);
    }

    /**
     * Extracts the column specifications from a given table.
     *
     * @param Table $table The table.
     * @return array<string, string> A map of column name to specification.
     * @psalm-return array<columnName, specification>
     */
    protected function extractColumnSpecifications(Table $table): array
    {
        $intFields = implode(
            '|',
            [
                'BIT',
                'DECIMAL',
                'DOUBLE',
                'FLOAT',
                'INT',
                'NUMERIC',
                'REAL',
            ]
        );

        $spec = [];
        foreach ($table->schema() as $key => $def) {
            $spec[$key] = preg_match("/^\s*[A-Z]*({$intFields})/i", $def)
                ? '%d'
                : '%s';
        }

        return $spec;
    }

    /**
     * finds the data specifications from the given column specifications.
     *
     * @param array<string, string> $columnSpecifications A map of column name to specification.
     * @psalm-param array<columnName, specification> $columnSpecifications
     * @param array $data The request data.
     * @psalm-param externalSiteConfig $data
     * @return string[] The list of specifications.
     */
    protected function findSpecifications(array $columnSpecifications, array $data): array
    {
        return array_map(
            static function (string $field) use ($columnSpecifications): string {
                return $columnSpecifications[$field] ?? '%s';
            },
            array_keys($data)
        );
    }

    /**
     * Validates the required data.
     *
     * @param array $data The request data.
     * @psalm-param externalSiteConfig $data
     * @return void
     * @throws RuntimeException if validation fails.
     */
    protected function validateData(array $data): void
    {
        foreach ($this->requiredColumnNames as $requiredColumnName) {
            if (!array_key_exists($requiredColumnName, $data) || !$data[$requiredColumnName]) {
                $fieldName = ucwords(str_replace("_", " ", $requiredColumnName));
                throw new RuntimeException(sprintf(
                // translators: %s: Human readable name of the field
                    __('Required field missing: %s', 'multilingualpress'),
                    $fieldName
                ));
            }
        }
    }
}
