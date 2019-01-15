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
            foreach ($this->getFilters() as $filter) {
                $content .= PHP_EOL . \call_user_func($filter, $this->loadFile($file), $this, $file);
            }
        }

        return $content;
    }
}
