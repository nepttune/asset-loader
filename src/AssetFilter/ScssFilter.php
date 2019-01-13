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

namespace Nepttune\AssetFilter;

use \WebLoader\Compiler;

class ScssFilter
{
    /**
     * Compile target code
     * @param string $code
     * @param Compiler $compiler
     * @return string
     */
    public function __invoke($code, Compiler $compiler)
    {
        $compiler = new \Leafo\ScssPhp\Compiler();
        return $compiler->compile($code);
    }
}
