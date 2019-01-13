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

namespace Nepttune\AssetFilter;

use \WebLoader\Compiler;

class JsMinFilter
{
    /**
     * Minify target code
     * @param string $code
     * @param Compiler $compiler
     * @return string
     */
    public function __invoke($code, Compiler $compiler)
    {
        $minifier = new \MatthiasMullie\Minify\JS($code);
        return $minifier->minify();
    }
}
