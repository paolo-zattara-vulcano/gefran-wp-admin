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

namespace Inpsyde\MultilingualPress\Framework\Auth;

use Inpsyde\MultilingualPress\Framework\Nonce\Nonce;
use WP_Comment;

/**
 * @package MultilingualPress
 * @license http://opensource.org/licenses/MIT MIT
 */
final class CommentAuth implements Auth
{
    /**
     * @var WP_Comment
     */
    private $comment;

    /**
     * @var Nonce
     */
    private $nonce;

    /**
     * @param WP_Comment $comment
     * @param Nonce $nonce
     */
    public function __construct(WP_Comment $comment, Nonce $nonce)
    {
        $this->comment = $comment;
        $this->nonce = $nonce;
    }

    /**
     * @inheritdoc
     */
    public function isAuthorized(): bool
    {
        $comment = get_comment($this->comment->comment_ID);

        if (!$comment instanceof WP_Comment || ms_is_switched()) {
            return false;
        }

        return current_user_can('edit_comment', $this->comment->comment_ID) && $this->nonce->isValid();
    }
}
