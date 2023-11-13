<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\ExternalSites\Integrations\Redirect;

use Inpsyde\MultilingualPress\Module\Redirect\RedirectTarget;
use RuntimeException;

/**
 * Can create RedirectTarget for external site.
 *
 * @psalm-type redirectTargetConfig = array{
 *      locale: string,
 *      priority: int,
 *      siteId: int,
 *      url: string,
 *      user_priority: float,
 *      language_fallback_priority: int
 * }
 */
interface ExternalSiteRedirectTargetFactoryInterface
{
    /**
     * Creates a new RedirectTarget instance with a given config.
     *
     * @param array $config The config.
     * @psalm-param redirectTargetConfig $config
     * @return RedirectTarget The new instance.
     * @throws RuntimeException If problem creating.
     */
    public function createExternalSiteRedirectTarget(array $config): RedirectTarget;
}
