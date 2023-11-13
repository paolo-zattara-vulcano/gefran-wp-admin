<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\ExternalSites\Integrations\Redirect;

use Inpsyde\MultilingualPress\Module\Redirect\RedirectTarget;

class ExternalSiteRedirectTargetFactory implements ExternalSiteRedirectTargetFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createExternalSiteRedirectTarget(array $config): RedirectTarget
    {
        return new RedirectTarget(
            [
                RedirectTarget::KEY_LANGUAGE => $config['locale'] ?? '',
                RedirectTarget::KEY_PRIORITY => $config['priority'] ?? 0,
                RedirectTarget::KEY_SITE_ID => $config['siteId'] ?? 0,
                RedirectTarget::KEY_URL => $config['url'] ?? '',
                RedirectTarget::KEY_USER_PRIORITY => $config['user_priority'] ?? 0.0,
                RedirectTarget::KEY_LANGUAGE_FALLBACK_PRIORITY => 1,
            ]
        );
    }
}
