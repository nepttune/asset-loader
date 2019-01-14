<?php

/**
 * This file is part of Nepttune (https://www.peldax.com)
 *
 * Copyright (c) 2018 Václav Pelíšek (info@peldax.com)
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <https://www.peldax.com>.
 */

declare(strict_types = 1);

namespace Nepttune\AssetMinifier;

class JsMinifier extends \MatthiasMullie\Minify\JS
{
    public function execute($path = null)
    {
        return parent::execute($path) . ';';
    }
}
