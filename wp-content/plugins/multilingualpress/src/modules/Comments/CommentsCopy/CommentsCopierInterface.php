<?php

# -*- coding: utf-8 -*-
/*
 * This file is part of the MultilingualPress package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Inpsyde\MultilingualPress\Module\Comments\CommentsCopy;

use RuntimeException;

/**
 * Can copy the comments to the given sites.
 */
interface CommentsCopierInterface
{
    /**
     * Copies the comments by given comment IDs from give source site ID to the sites by given site IDs.
     *
     * @param int $sourceSiteId The source site ID.
     * @param int[] $sourceCommentIds The list of comment IDs.
     * @param int[] $remoteSiteIds The list of site IDs.
     * @throws RuntimeException If problem copying.
     */
    public function copyCommentsToSites(int $sourceSiteId, array $sourceCommentIds, array $remoteSiteIds): void;
}
