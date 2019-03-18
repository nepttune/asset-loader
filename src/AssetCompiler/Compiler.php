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

namespace Nepttune\AssetCompiler;

class Compiler extends \WebLoader\Compiler
{
    public function getContent(?array $files = null) : string
    {
        if ($files === null) {
            $files = $this->getFileCollection()->getFiles();
        }

        $content = '';
        foreach ($files as $file) {
            // apply filters
            $temp = $this->loadFile($file);
            foreach ($this->getFilters() as $filter) {
                $temp = \call_user_func($filter, $temp, $this, $file);
            }
            $content .= $temp . PHP_EOL;
        }

        return $content;
    }
}
