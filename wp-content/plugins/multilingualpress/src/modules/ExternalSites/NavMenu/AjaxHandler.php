<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\ExternalSites\NavMenu;

use Inpsyde\MultilingualPress\Framework\Http\Request;
use Inpsyde\MultilingualPress\Framework\Nonce\Nonce;
use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSite\ExternalSiteInterface;

class AjaxHandler
{
    public const ACTION = 'multilingualpress_add_external_sites_to_nav_menu';

    /**
     * @var Nonce
     */
    protected $nonce;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ExternalSiteMenuItemFactoryInterface
     */
    protected $externalSiteMenuItemFactory;

    /**
     * @var ExternalSiteInterface[]
     */
    protected $allExternalSites;

    public function __construct(
        Nonce $nonce,
        Request $request,
        ExternalSiteMenuItemFactoryInterface $externalSiteMenuItemFactory,
        array $allExternalSites
    ) {

        $this->nonce = $nonce;
        $this->request = $request;
        $this->externalSiteMenuItemFactory = $externalSiteMenuItemFactory;
        $this->allExternalSites = $allExternalSites;
    }

    /**
     * Handles the AJAX request and sends an appropriate response.
     */
    public function handle(): void
    {
        if (!wp_doing_ajax()) {
            return;
        }

        if (!doing_action('wp_ajax_' . self::ACTION)) {
            wp_send_json_error();
        }

        $externalSites = $this->externalSiteIdsFromRequest();

        if (empty($externalSites)) {
            wp_send_json_error();
        }

        $menuId = (int)$this->request->bodyValue(
            'menu',
            INPUT_POST,
            FILTER_SANITIZE_NUMBER_INT
        );

        if (!$menuId) {
            wp_send_json_error();
        }

        $items = [];

        foreach ($this->allExternalSites as $externalSite) {
            if (!in_array($externalSite->id(), $externalSites, true)) {
                continue;
            }

            $items[] = $this->externalSiteMenuItemFactory->createExternalSiteMenuItem($menuId, $externalSite);
        }

        /**
         * Contains the Walker_Nav_Menu_Edit class.
         */
        require_once ABSPATH . 'wp-admin/includes/nav-menu.php';

        wp_send_json_success(
            walk_nav_menu_tree(
                $items,
                0,
                (object)[
                    'after' => '',
                    'before' => '',
                    'link_after' => '',
                    'link_before' => '',
                    'walker' => new \Walker_Nav_Menu_Edit(),
                ]
            )
        );
    }

    /**
     * Gets the list of external site IDs from request.
     *
     * @return int[] The list of external site IDs.
     */
    protected function externalSiteIdsFromRequest(): array
    {
        if (!current_user_can('edit_theme_options') || !$this->nonce->isValid()) {
            return [];
        }

        $externalSites = $this->request->bodyValue(
            'mlp_external_sites',
            INPUT_POST,
            FILTER_SANITIZE_NUMBER_INT,
            FILTER_FORCE_ARRAY
        );

        return $externalSites ? array_filter(wp_parse_id_list($externalSites)) : [];
    }
}
