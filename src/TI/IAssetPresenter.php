<?php

/**
 * This file is part of Nepttune (https://www.peldax.com)
 *
 * Copyright (c) 2019 Václav Pelíšek (info@peldax.com)
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <https://www.peldax.com>.
 */

declare(strict_types = 1);

namespace Nepttune\TI;

interface IAssetPresenter
{
    public function injectAssetPresenter(\Nepttune\Component\IAssetLoaderFactory $IAssetLoaderFactory);

    public function getModule() : string;

    public function getNameWM() : string;

    public static function getPhotoswipe() : string;
}
