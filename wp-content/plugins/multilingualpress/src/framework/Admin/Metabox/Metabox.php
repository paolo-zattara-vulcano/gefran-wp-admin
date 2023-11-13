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

namespace Inpsyde\MultilingualPress\Framework\Admin\Metabox;

use Inpsyde\MultilingualPress\Framework\Entity;

/**
 * @package MultilingualPress
 * @license http://opensource.org/licenses/MIT MIT
 */
interface Metabox
{
    const SAVE = 'save';
    const SHOW = 'show';

    /**
     * @param string $showOrSave
     * @param Entity $entity
     * @return Info
     */
    public function createInfo(string $showOrSave, Entity $entity): Info;

    /**
     * Returns the site ID for the meta box.
     * @return int
     */
    public function siteId(): int;

    /**
     * Create an instance of Action for the given entity.
     *
     * @param Entity $entity
     * @return Action
     */
    public function action(Entity $entity): Action;

    /**
     * Check if the given entity is a valid one to be in the metabox.
     *
     * @param Entity $entity
     * @return bool true if is valid, otherwise false.
     */
    public function isValid(Entity $entity): bool;

    /**
     * Create the metabox view for a given entity.
     *
     * @param Entity $entity
     * @return View
     */
    public function view(Entity $entity): View;
}
