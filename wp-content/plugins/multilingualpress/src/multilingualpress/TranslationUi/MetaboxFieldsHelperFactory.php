<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\TranslationUi;

class MetaboxFieldsHelperFactory implements MetaboxFieldsHelperFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createMetaboxFieldsHelper(int $siteId): MetaboxFieldsHelperInterface
    {
        return new MetaboxFieldsHelper($siteId);
    }
}
