<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\TranslationUi;

use RuntimeException;

/**
 * Can create helper for translation metabox fields.
 */
interface MetaboxFieldsHelperFactoryInterface
{
    /**
     * Creates a new metabox fields helper instance for given site.
     *
     * @param int $siteId The site ID.
     * @return MetaboxFieldsHelperInterface The new helper instance.
     * @throws RuntimeException If problem creating.
     */
    public function createMetaboxFieldsHelper(int $siteId): MetaboxFieldsHelperInterface;
}
