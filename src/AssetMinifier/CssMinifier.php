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

class CssMinifier extends \MatthiasMullie\Minify\CSS
{
    protected function importFiles($source, $content)
    {
        $importPath = '/webloader/' . \time() . '/';

        $regex = '/url\((["\']?)(.+?)\\1\)/i';
        if (\preg_match_all($regex, $content, $matches, PREG_SET_ORDER)) {
            $search = [];
            $replace = [];

            foreach ($matches as $match)
            {
                $extraUrl = '';

                $param = \strpos($match[2], '?');
                $hash = \strpos($match[2], '#');
                if ($param !== false) {
                    $match[2] = \substr($match[2], 0, $param);
                    $extraUrl = \substr($match[2], $param);
                }
                elseif ($hash !== false) {
                    $match[2] = \substr($match[2], 0, $hash);
                    $extraUrl = \substr($match[2], $hash);
                }

                $path = \dirname($source) . '/' . $match[2];

                if (!\file_exists($path)) {
                    continue;
                }

                $extension = \substr(\strrchr($match[2], '.'), 1);
                if (\array_key_exists($extension, $this->importExtensions) &&
                    $this->canImportFile($path) &&
                    $this->canImportBySize($path))
                {
                    continue;
                }

                if (!\file_exists(getcwd() . $importPath)) {
                    \mkdir(getcwd() . $importPath, 0777, true);
                }

                $target = $importPath . \basename($path);

                \copy($path, \getcwd() . $target);

                $search[] = $match[0];
                $replace[] = 'url(' . $target . $extraUrl . ')';

            }

            $content = str_replace($search, $replace, $content);
        }

        return parent::importFiles($source, $content);
    }
    
    public function addWithPath(string $code, string $path) : self
    {
        $this->data[$path] = str_replace(array("\r\n", "\r"), "\n", $code);

        return $this;
    }
}
