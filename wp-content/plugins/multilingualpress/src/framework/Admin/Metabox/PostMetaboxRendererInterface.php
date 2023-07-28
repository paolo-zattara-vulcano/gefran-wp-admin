<?php

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Framework\Admin\Metabox;

/**
 * Can render post metaboxes.
 */
interface PostMetaboxRendererInterface
{
    /**
     * Renders the markup for given post ID.
     *
     * @param int $postId The post ID.
     */
    public function render(int $postId): void;
}
