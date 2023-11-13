<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSitesRepository;

use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSite\ExternalSiteInterface;
use RuntimeException;

/**
 * Represents the repository for ExternalSites.
 *
 * @psalm-type externalSiteData = array{
 *      ID: int,
 *      site_url: string,
 *      site_language_name: string,
 *      site_language_locale: string,
 *      enable_hreflang: int,
 *      site_redirect: int
 * }
 */
interface ExternalSitesRepositoryInterface
{
    /**
     * Deletes the external site with the given ID.
     *
     * @param int $id The external site ID.
     * @throws RuntimeException If problem deleting.
     */
    public function deleteExternalSite(int $id): void;

    /**
     * Returns the list of all existing external sites.
     *
     * @return ExternalSiteInterface[] The list of all existing external sites.
     * @throws RuntimeException If problem returning.
     */
    public function allExternalSites(): array;

    /**
     * Inserts the external site entry according to the given data.
     *
     * @param array $externalSiteData The requested external site data.
     * @psalm-param externalSiteData $externalSiteData
     * @throws RuntimeException If problem inserting.
     */
    public function insertExternalSite(array $externalSiteData): void;

    /**
     * Updates the external site entry according to the given data.
     *
     * @param int $siteId The external site ID to update.
     * @param array $externalSiteData The requested external site data.
     * @psalm-param externalSiteData $externalSiteData
     * @throws RuntimeException If problem updating.
     */
    public function updateExternalSite(int $siteId, array $externalSiteData): void;

    /**
     * Returns the external site for the given column.
     *
     * @param string $column The column name.
     * @param string|int $value The column value.
     * @return ExternalSiteInterface|null The external site or null if it doesn't exist with the given params.
     * @throws RuntimeException If problem returning.
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     */
    public function externalSiteBy(string $column, $value): ?ExternalSiteInterface;

    /**
     * Returns the auto increment value from external sites table.
     *
     * @return int
     */
    public function autoIncrementValue(): int;
}
