<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSitesMetaBox;

use Inpsyde\MultilingualPress\Module\ExternalSites\ExternalSite\ExternalSiteInterface;

/**
 * Can render an External sites MetaBox.
 */
interface ExternalSitesMetaBoxViewInterface
{
    /**
     * Renders the given external sites MetaBox HTML markup for given post.
     *
     * @param ExternalSiteInterface[] $externalSites The list of external sites.
     * @param int $postId The post ID.
     */
    public function render(array $externalSites, int $postId): void;
}
