<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\ExternalSites\Settings;

use Inpsyde\MultilingualPress\Database\Table\ExternalSitesTable;
use Inpsyde\MultilingualPress\Framework\Admin\PersistentAdminNotices;
use Inpsyde\MultilingualPress\Framework\Http\Request;
use Inpsyde\MultilingualPress\Framework\Nonce\Nonce;
use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSite\ExternalSiteInterface;
use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSitesRepository\ExternalSitesRepositoryInterface;
use Inpsyde\MultilingualPress\Module\ExternalSites\ServiceProvider as ExternalSitesModule;
use RuntimeException;

use function Inpsyde\MultilingualPress\redirectAfterSettingsUpdate;
use function Inpsyde\MultilingualPress\settingsErrors;

/**
 * @psalm-type Action = 'insert'|'update'|'delete'
 *
 * @psalm-type Item = array{
 *      site_url: string,
 *      site_language_name: string,
 *      site_language_locale: string,
 *      site_redirect?: int,
 *      enable_hreflang?: int
 * }
 */
class RequestHandler
{
    public const ACTION = 'update_multilingualpress_external_sites';
    public const ACTION_AFTER_EXTERNAL_SITE_IS_DELETED = 'multilingualpress.after_external_site_is_deleted';

    /**
     * @var Nonce
     */
    protected $nonce;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ExternalSitesRepositoryInterface
     */
    protected $externalSitesRepository;

    /**
     * @var PersistentAdminNotices
     */
    protected $notices;

    public function __construct(
        Request $request,
        Nonce $nonce,
        ExternalSitesRepositoryInterface $externalSitesRepository,
        PersistentAdminNotices $notices
    ) {

        $this->request = $request;
        $this->nonce = $nonce;
        $this->externalSitesRepository = $externalSitesRepository;
        $this->notices = $notices;
    }

    /**
     * Handles the POST requests.
     */
    public function handlePostRequest(): void
    {
        if (!$this->nonce->isValid()) {
            wp_die('Invalid', 'Invalid', 403);
        }

        $externalSites = $this->request->bodyValue('externalSites', INPUT_POST);

        $this->configureExternalSitesRequestData($externalSites);
        $splittedSites = $this->splitExternalSites($externalSites);
        if (!$splittedSites) {
            return;
        }

        $this->processAction('insert', $splittedSites['toInsert'] ?? []);
        $this->processAction('update', $splittedSites['toUpdate'] ?? []);
        $this->processAction('delete', $splittedSites['toDelete'] ?? []);

        redirectAfterSettingsUpdate();
    }

    /**
     * Process the given action(insert, update, delete) with the given data items.
     *
     * @param string $action The action name, can be insert, update or delete.
     * @psalm-param Action $action
     * @param array $items The list of items.
     * @psalm-param Item[] $items
     * phpcs:disable Inpsyde.CodeQuality.NestingLevel.High
     */
    protected function processAction(string $action, array $items): void
    {
        // phpcs:enable

        foreach ($items as $id => $values) {
            if (!$values) {
                continue;
            }

            try {
                switch ($action) {
                    case 'insert':
                        $this->externalSitesRepository->insertExternalSite($values);
                        break;
                    case 'update':
                        $this->externalSitesRepository->updateExternalSite($id, $values);
                        break;
                    case 'delete':
                        $this->externalSitesRepository->deleteExternalSite($id);
                        /**
                         * Fires after the external site is deleted.
                         *
                         * @param int $id
                         */
                        do_action(self::ACTION_AFTER_EXTERNAL_SITE_IS_DELETED, $id);
                        break;
                    default:
                        throw new RuntimeException('Wrong action');
                }
            } catch (RuntimeException $exception) {
                settingsErrors([ExternalSitesModule::MODULE_ID => $exception->getMessage()], ExternalSitesModule::MODULE_ID, 'error');
            }
        }
    }

    /**
     * Splits the request data into the map of appropriate action names to a list of external site items.
     *
     * @param array $externalSites The list of external site items.
     * @psalm-param Item[] $externalSites
     * @return array A map of appropriate action name to a list of external site items.
     */
    protected function splitExternalSites(array $externalSites): array
    {
        $splittedExternalSites = [
            'toInsert' => [],
            'toUpdate' => [],
            'toDelete' => [],
        ];

        $indexes = array_map(
            static function (ExternalSiteInterface $item): int {
                return $item->id();
            },
            $this->externalSitesRepository->allExternalSites()
        );

        foreach ($indexes as $index) {
            $isNegative = array_key_exists(0 - $index, $externalSites);
            $index = $isNegative ? 0 - $index : $index;
            $current = $externalSites[$index] ?? null;

            if (!$current) {
                continue;
            }

            if ($isNegative) {
                $splittedExternalSites['toDelete'][absint($index)] = $current;
                continue;
            }

            $splittedExternalSites['toUpdate'][$index] = $current;
        }

        foreach ($externalSites as $index => $item) {
            $filterEmpty = array_diff_key($item, [ExternalSitesTable::COLUMN_DISPLAY_STYLE => '']);
            if (empty(array_filter($filterEmpty))) {
                continue;
            }

            if (
                $index > 0
                && !array_key_exists($index, $splittedExternalSites['toDelete'])
                && !array_key_exists($index, $splittedExternalSites['toUpdate'])
            ) {
                $splittedExternalSites['toInsert'][$index] = $item;
            }
        }

        return $splittedExternalSites;
    }

    /**
     * Configures the external site's request data.
     *
     * @param array $externalSites The list of external site items.
     * @psalm-param Item[] $externalSites
     */
    protected function configureExternalSitesRequestData(array &$externalSites)
    {
        array_walk($externalSites, static function (array &$externalSite) {
            $externalSite[ExternalSitesTable::COLUMN_REDIRECT] =
                array_key_exists('site_redirect', $externalSite) ? 1 : 0;
            $externalSite[ExternalSitesTable::COLUMN_ENABLE_HREFLANG] =
                array_key_exists('enable_hreflang', $externalSite) ? 1 : 0;
        });
    }
}
