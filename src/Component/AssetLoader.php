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

namespace Nepttune\Component;

final class AssetLoader extends \Nette\Application\UI\Control implements IStyleLists, IScriptLists
{
    /** @var string */
    protected $vapidPublicKey;

    /** @var string */
    protected $googleApiKey;

    /** @var \Nette\Caching\Cache */
    protected $cache;

    /** @var bool */
    protected $admin;

    /** @var  string */
    protected $module;

    /** @var  string */
    protected $presen;

    /** @var  string */
    protected $action;

    /** @var bool */
    protected $maps;

    /** @var bool */
    protected $recaptcha;

    /** @var bool */
    protected $subscribe;

    /** @var bool */
    protected $photoswipe;

    public function __construct(string $vapidPublicKey, string $googleApiKey, \Nette\Caching\IStorage $storage)
    {
        parent::__construct();

        $this->vapidPublicKey = $vapidPublicKey;
        $this->googleApiKey = $googleApiKey;
        $this->cache = new \Nette\Caching\Cache($storage, 'Nepttune.AssetLoader');
    }

    protected function attached($presenter) : void
    {
        $this->admin =
            \class_exists('\Nepttune\Presenter\BaseAuthPresenter') &&
            $presenter instanceof \Nepttune\Presenter\BaseAuthPresenter;
        $this->module = $presenter->getModule();
        $this->presen = $presenter->getName();
        $this->action = $presenter->getAction();

        $this->maps = $presenter->assetsMaps;
        $this->recaptcha = $presenter->assetsRecaptcha;
        $this->subscribe = $presenter->assetsSubscribe;
        $this->photoswipe = $presenter->assetsPhotoswipe;
    }

    public function renderHead() : void
    {
        $assets = $this->getAssetsHead();
        $styles = [];

        if ($assets['lib']['css']) {
            $styles[] = self::compileStyles($assets['lib']['css']);
        }
        if ($assets['asset']['css']) {
            $styles[] = self::compileStyles($assets['asset']['css']);
        }

        $this->template->styles = $styles;

        $this->template->setFile(__DIR__ . '/AssetLoaderHead.latte');
        $this->template->render();
    }

    public function renderBody() : void
    {
        $this->template->maps = $this->maps && (bool) \strlen($this->googleApiKey);
        $this->template->recaptcha = $this->recaptcha;
        $this->template->subscribe = $this->subscribe && (bool) \strlen($this->vapidPublicKey);
        $this->template->photoswipe = $this->photoswipe;

        $this->template->mapsKey = $this->googleApiKey;
        $this->template->workerKey = $this->vapidPublicKey;

        $assets = $this->getAssetsBody();
        $styles = [];
        $scripts = [];

        if ($assets['lib']['css']) {
            $styles[] = self::compileStyles($assets['lib']['css']);
        }
        if ($assets['asset']['css']) {
            $styles[] = self::compileStyles($assets['asset']['css']);
        }

        if ($assets['lib']['js']) {
            $scripts[] = self::compileScripts($assets['lib']['js']);
        }
        if ($assets['asset']['js']) {
            $scripts[] = self::compileScripts($assets['asset']['js']);
        }

        $this->template->styles = $styles;
        $this->template->scripts = $scripts;

        $this->template->setFile(__DIR__ . '/AssetLoaderBody.latte');
        $this->template->render();

    }

    public function getIntegrity(string $path) : string
    {
        return $this->cache->call('Nepttune\Component\AssetLoader::generateChecksum', $path);
    }

    public static function generateChecksum(string $path) : string
    {
        return 'sha256-' . \base64_encode(\hash_file('sha256', \getcwd() . $path, true));
    }

    public function getAssetsHead() : array
    {
        $cacheName = "{$this->module}_{$this->presen}_{$this->action}_head";
        $assets = $this->cache->load($cacheName);

        if ($assets) {
            return $assets;
        }
        
        $styles = [];
        $libStyles = static::STYLE_HEAD;

        if ($this->admin) {
            $styles = \array_merge($styles, static::STYLE_HEAD_ADMIN);
        }
        else {
            $styles = \array_merge($styles, static::STYLE_HEAD_FRONT);
        }

        if ($this->module) {
            $moduleStyle = '/scss/module/' . $this->module . '.scss';
            if (\file_exists(\getcwd() . '/node_modules/nepttune' . $moduleStyle)) {
                $styles[] = '/node_modules/nepttune' . $moduleStyle;
            }
            if (\file_exists(\getcwd() . $moduleStyle)) {
                $styles[] = '/www' . $moduleStyle;
            }
        }

        $presenStyle = '/scss/presenter/' . $this->presen . '.scss';
        if (\file_exists(\getcwd() . '/node_modules/nepttune' . $presenStyle)) {
            $styles[] = '/node_modules/nepttune' . $presenStyle;
        }
        if (\file_exists(\getcwd() . $presenStyle)) {
            $styles[] = '/www' . $presenStyle;
        }

        $actionStyle = '/scss/action/' . $this->presen . '/' . $this->action . '.scss';
        if (\file_exists(\getcwd() . '/node_modules/nepttune' . $actionStyle)) {
            $styles[] = '/node_modules/nepttune' . $actionStyle;
        }
        if (\file_exists(\getcwd() . $actionStyle)) {
            $styles[] = '/www' . $actionStyle;
        }

        $assets = [
            'lib' => ['css' => $libStyles],
            'asset' => ['css' => $styles],
        ];

        $this->cache->save($cacheName, $assets);
        return $assets;
    }

    public function getAssetsBody() : array
    {
        $cacheName = "{$this->module}_{$this->presen}_{$this->action}_body";
        $assets = $this->cache->load($cacheName);

        if ($assets) {
            return $assets;
        }

        $styles = [];
        $libStyles = static::STYLE_BODY;
        $scripts = [];
        $libScripts = static::SCRIPT_BODY;

        if ($this->admin) {
            $libStyles = \array_merge($libStyles, static::STYLE_BODY_ADMIN);
            $libScripts = \array_merge($libScripts, static::SCRIPT_BODY_ADMIN);
        }
        else {
            $libStyles = \array_merge($libStyles, static::STYLE_BODY_FRONT);
            $libScripts = \array_merge($libScripts, static::SCRIPT_BODY_FRONT);
        }

        $hasForm = false;
        $hasList = false;
        $hasStat = false;

        foreach ($this->getPresenter()->getComponents() as $name => $component) {
            $componentStyle = '/scss/component/' . \ucfirst($name) . '.scss';
            $componentScript = '/js/component/' . \ucfirst($name) . '.js';

            if (\file_exists(\getcwd() . '/node_modules/nepttune' . $componentStyle)) {
                $styles[] = '/node_modules/nepttune' . $componentStyle;
            }
            if (\file_exists(\getcwd() . $componentStyle)) {
                $styles[] = '/www' . $componentStyle;
            }

            if (\file_exists(\getcwd() . '/node_modules/nepttune' . $componentScript)) {
                $scripts[] = '/node_modules/nepttune' . $componentScript;
            }
            if (\file_exists(\getcwd() . $componentScript)) {
                $scripts[] = '/www' . $componentScript;
            }

            if (!$hasForm && strpos($name, 'Form') !== false) {
                $hasForm = true;
            }

            if (!$hasList && strpos($name, 'List') !== false) {
                $hasForm = true;
                $hasList = true;
            }

            if (!$hasStat && strpos($name, 'Stat') !== false) {
                $hasStat = true;
            }
        }

        if ($hasForm) {
            $libStyles = \array_merge($libStyles, static::STYLE_FORM);
            $libScripts = \array_merge($libScripts, static::SCRIPT_FORM);
        }

        if ($hasList) {
            $libStyles = \array_merge($libStyles, static::STYLE_LIST);
            $libScripts = \array_merge($libScripts, static::SCRIPT_LIST);
        }

        if ($hasStat) {
            $libStyles = \array_merge($libStyles, static::STYLE_STAT);
            $libScripts = \array_merge($libScripts, static::SCRIPT_STAT);
        }

        if ($this->module) {
            $moduleScript = '/js/module/' . $this->module . '.js';
            if (\file_exists(\getcwd() . '/node_modules/nepttune' . $moduleScript)) {
                $scripts[] = '/node_modules/nepttune' . $moduleScript;
            }
            if (\file_exists(\getcwd() . $moduleScript)) {
                $scripts[] = '/www' . $moduleScript;
            }
        }

        $presenScript = '/js/presenter/' . $this->presen . '.js';
        if (\file_exists(\getcwd() . '/node_modules/nepttune' . $presenScript)) {
            $scripts[] = '/node_modules/nepttune' . $presenScript;
        }
        if (\file_exists(\getcwd() . $presenScript)) {
            $scripts[] = '/www' . $presenScript;
        }

        $actionScript = '/js/action/' . $this->presen . '/' . $this->action . '.js';
        if (\file_exists(\getcwd() . '/node_modules/nepttune' . $actionScript)) {
            $scripts[] = '/node_modules/nepttune' . $actionScript;
        }
        if (\file_exists(\getcwd() . $actionScript)) {
            $scripts[] = '/www' . $actionScript;
        }

        $assets = [
            'lib' => ['css' => $libStyles, 'js' => $libScripts],
            'asset' => ['css' => $styles, 'js' => $scripts],
        ];

        $this->cache->save($cacheName, $assets);
        return $assets;
    }

    private static function compileStyles(array $styles) : string
    {
        $files = new \WebLoader\FileCollection(\getcwd() . '/../');
        $files->addFiles($styles);
        
        $compiler = \Nepttune\AssetCompiler\Compiler::createCssCompiler($files, \getcwd() . '/webloader/');
        $compiler->setCheckLastModified(false);
        $compiler->setJoinFiles(true);
        $compiler->addFilter(new \Nepttune\AssetFilter\CssMinFilter());

        return '/webloader/' . $compiler->generate()[0]->file;
    }

    private static function compileScssStyles(array $styles) : string
    {
        $files = new \WebLoader\FileCollection(\getcwd() . '/../');
        $files->addFiles($styles);
        
        $compiler = \Nepttune\AssetCompiler\Compiler::createCssCompiler($files, \getcwd() . '/webloader/');
        $compiler->setCheckLastModified(false);
        $compiler->setJoinFiles(true);
        $compiler->addFilter(new \WebLoader\Filter\ScssFilter());
        $compiler->addFilter(new \Nepttune\AssetFilter\CssMinFilter());

        return '/webloader/' . $compiler->generate()[0]->file;
    }

    private static function compileScripts(array $scripts) : string
    {
        $files = new \WebLoader\FileCollection(\getcwd() . '/../');
        $files->addFiles($scripts);
        
        $compiler = \Nepttune\AssetCompiler\Compiler::createJsCompiler($files, \getcwd() . '/webloader/');
        $compiler->setCheckLastModified(false);
        $compiler->setJoinFiles(true);
        $compiler->addFilter(new \Nepttune\AssetFilter\JsMinFilter());

        return '/webloader/' . $compiler->generate()[0]->file;
    }
}
