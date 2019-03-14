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

trait TAssetPresenter
{
    /** @var bool */
    public $assetsPhotoswipe = false;

    /** @var bool */
    public $assetsSubscribe = false;

    /** @var bool */
    public $assetsRecaptcha = false;

    /** @var bool */
    public $assetsMaps = false;

    /** @var array */
    public $additionalStyles = [];
    
    /** @var array */
    public $additionalScripts = [];
    
    /** @var string */
    public $module;

    /** @var string */
    public $nameWM;

    /** @var \Nepttune\Component\IAssetLoaderFactory */
    protected $iAssetLoaderFactory;

    public function injectAssetPresenter(\Nepttune\Component\IAssetLoaderFactory $IAssetLoaderFactory)
    {
        $this->iAssetLoaderFactory = $IAssetLoaderFactory;
    }

    protected function createComponentAssetLoader() : \Nepttune\Component\AssetLoader
    {
        return $this->iAssetLoaderFactory->create();
    }

    public function getModule() : string
    {
        if (!$this->module) {
            $pos = \strpos($this->getName(), ':');
            $this->module = $pos === false ? '' : \substr($this->getName(), 0, $pos);
        }

        return $this->module;
    }

    public function getNameWM() : string
    {
        if (!$this->nameWM) {
            $pos = \strpos($this->getName(), ':');
            $this->nameWM = $pos === false ? $this->getName() : \substr($this->getName(), $pos + 1);
        }

        return $this->nameWM;
    }

    public static function getPhotoswipe() : string
    {
        return __DIR__ . '/../../../nepttune/src/templates/photoswipe.latte';
    }
}
