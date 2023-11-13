<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Framework\Admin;

/**
 * Represents the custom column for MultilingualPress in admin entity list view.
 */
interface TranslationColumnInterface
{
    /**
     * The name of the column.
     *
     * @return string
     */
    public function name(): string;

    /**
     * The title of the column.
     *
     * @return string
     */
    public function title(): string;

    /**
     * The value of the column for given entity ID.
     *
     * @param int $id The entity ID.
     * @return string
     */
    public function value(int $id): string;
}
