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

final class AssetLoader extends \Nette\Application\UI\Control
{
    private static $defaultConfig = [
        'global' => [
            'styleHead' => [],
            'styleBody' => [],
            'script' => [],
        ],
        'module' => [
            'www' => [
                'styleHead' => [],
                'styleBody' => [],
                'script' => [],
            ],
            'admin' => [
                'styleHead' => [],
                'styleBody' => [],
                'script' => [],
            ],
        ],
        'form' => [
            'styleBody' => [],
            'script' => [],
        ],
        'list' => [
            'styleBody' => [],
            'script' => [],
        ],
        'stat' => [
            'styleBody' => [],
            'script' => [],
        ],
    ];

    /** @var array */
    protected $config;

    /** @var string */
    protected $version;

    /** @var string */
    protected $vapidPublicKey;

    /** @var string */
    protected $googleApiKey;

    /** @var \Nette\Caching\Cache */
    protected $cache;

    /** @var \Nepttune\TI\TAssetPresenter */
    protected $presenter;

    public function __construct(
        array $config,
        string $version,
        string $vapidPublicKey,
        string $googleApiKey,
        \Nette\Caching\IStorage $storage)
    {
        $this->monitor(\Nette\Application\UI\Presenter::class, function (\Nette\ComponentModel\IComponent $presenter) {
            if (!$presenter instanceof \Nepttune\TI\IAssetPresenter) {
                throw new \Nette\InvalidStateException('Presenter doesnt implement IAssetPresenter interface');
            }

            $this->presenter = $presenter;
        });

        $this->config = \array_merge_recursive(self::$defaultConfig, $config);
        $this->version = \implode(\explode('.', $version));
        $this->vapidPublicKey = $vapidPublicKey;
        $this->googleApiKey = $googleApiKey;
        $this->cache = new \Nette\Caching\Cache($storage, 'Nepttune.AssetLoader');
    }

    public function renderHead() : void
    {
        $assets = $this->getAssetsHead();

        $this->template->styles = $assets['style'];
        $this->template->version = $this->version;
        $this->template->setFile(__DIR__ . '/AssetLoaderHead.latte');
        $this->template->render();
    }

    public function renderBody() : void
    {
        $assets = $this->getAssetsBody();

        $this->template->maps = $this->presenter->assetsMaps && (bool) \strlen($this->googleApiKey);
        $this->template->recaptcha = $this->presenter->assetsRecaptcha;
        $this->template->subscribe = $this->presenter->assetsSubscribe && (bool) \strlen($this->vapidPublicKey);
        $this->template->photoswipe = $this->presenter->assetsPhotoswipe;
        $this->template->mapsKey = $this->googleApiKey;
        $this->template->workerKey = $this->vapidPublicKey;
        $this->template->styles = $assets['style'];
        $this->template->scripts = $assets['script'];
        $this->template->version = $this->version;
        $this->template->setFile(__DIR__ . '/AssetLoaderBody.latte');
        $this->template->render();

    }

    public static function generateChecksum(string $path) : string
    {
        return 'sha256-' . \base64_encode(\hash_file('sha256', \getcwd() . $path, true));
    }

    public function getAssetsHead() : array
    {
        $cacheName = "{$this->presenter->getModule()}_{$this->presenter->getName()}_{$this->presenter->getAction()}_head";
        $assets = $this->cache->load($cacheName);

        if ($assets) {
            return $assets;
        }

        $module = $this->config['module'][\lcfirst($this->presenter->getModule())]['styleHead'] ?? [];
        if ($this->presenter->getModule()) {
            $moduleStyle = '/scss/module/' . $this->presenter->getModule() . '.scss';
            if (\file_exists(\getcwd() . '/../node_modules/nepttune' . $moduleStyle)) {
                $module[] = '/node_modules/nepttune' . $moduleStyle;
            }
            if (\file_exists(\getcwd() . $moduleStyle)) {
                $module[] = '/www' . $moduleStyle;
            }
        }

        $presenter = [];
        $presenStyle = '/scss/presenter/' . $this->presenter->getName() . '.scss';
        if (\file_exists(\getcwd() . '/../node_modules/nepttune' . $presenStyle)) {
            $presenter[] = '/node_modules/nepttune' . $presenStyle;
        }
        if (\file_exists(\getcwd() . $presenStyle)) {
            $presenter[] = '/www' . $presenStyle;
        }

        $action = [];
        $actionStyle = '/scss/action/' . $this->presenter->getName() . '/' . $this->presenter->getAction() . '.scss';
        if (\file_exists(\getcwd() . '/../node_modules/nepttune' . $actionStyle)) {
            $action[] = '/node_modules/nepttune' . $actionStyle;
        }
        if (\file_exists(\getcwd() . $actionStyle)) {
            $action[] = '/www' . $actionStyle;
        }

        $assets = [
            'style' => self::compileStyles([$this->config['global']['styleHead'], $module, $presenter, $action]),
        ];

        $this->cache->save($cacheName, $assets);
        return $assets;
    }

    public function getAssetsBody() : array
    {
        $cacheName = "{$this->presenter->getModule()}_{$this->presenter->getName()}_{$this->presenter->getAction()}_body";
        $assets = $this->cache->load($cacheName);

        if ($assets) {
            return $assets;
        }

        if (\array_key_exists(\lcfirst($this->presenter->getModule()), $this->config['module'])) {
        	$module = $this->config['module'][\lcfirst($this->presenter->getModule())]['script'];
	        if ($this->presenter->getModule()) {
	            $moduleStyle = '/js/module/' . $this->presenter->getModule() . '.js';
	            if (\file_exists(\getcwd() . '/../node_modules/nepttune' . $moduleStyle)) {
	                $module[] = '/node_modules/nepttune' . $moduleStyle;
	            }
	            if (\file_exists(\getcwd() . $moduleStyle)) {
	                $module[] = '/www' . $moduleStyle;
	            }
	        }
        }

        $presenter = [];
        $presenScript = '/js/presenter/' . $this->presenter->getName() . '.js';
        if (\file_exists(\getcwd() . '/../node_modules/nepttune' . $presenScript)) {
            $presenter[] = '/node_modules/nepttune' . $presenScript;
        }
        if (\file_exists(\getcwd() . $presenScript)) {
            $presenter[] = '/www' . $presenScript;
        }

        $action = [];
        $actionScript = '/js/action/' . $this->presenter->getName() . '/' . $this->presenter->getAction() . '.js';
        if (\file_exists(\getcwd() . '/../node_modules/nepttune' . $actionScript)) {
            $action[] = '/node_modules/nepttune' . $actionScript;
        }
        if (\file_exists(\getcwd() . $actionScript)) {
            $action[] = '/www' . $actionScript;
        }

        $componentStyles = [];
        $componentScripts = [];
        $hasForm = false;
        $hasList = false;
        $hasStat = false;

        foreach ($this->getPresenter()->getComponents() as $name => $component) {
            $componentStyle = '/scss/component/' . \ucfirst($name) . '.scss';
            $componentScript = '/js/component/' . \ucfirst($name) . '.js';

            $styles = [];
            if (\file_exists(\getcwd() . '/../node_modules/nepttune' . $componentStyle)) {
                $styles[] = '/node_modules/nepttune' . $componentStyle;
            }
            if (\file_exists(\getcwd() . $componentStyle)) {
                $styles[] = '/www' . $componentStyle;
            }
            if (!empty($styles)) {
                $componentStyles[] = $styles;
            }

            $scripts = [];
            if (\file_exists(\getcwd() . '/../node_modules/nepttune' . $componentScript)) {
                $scripts[] = '/node_modules/nepttune' . $componentScript;
            }
            if (\file_exists(\getcwd() . $componentScript)) {
                $scripts[] = '/www' . $componentScript;
            }
            if (!empty($scripts)) {
                $componentScripts[] = $scripts;
            }

            if (!$hasForm && \strpos($name, 'Form') !== false) {
                $hasForm = true;
            }

            if (!$hasList && \strpos($name, 'List') !== false) {
                $hasForm = true;
                $hasList = true;
            }

            if (!$hasStat && \strpos($name, 'Stat') !== false) {
                $hasStat = true;
            }
        }

        $assets = [
            'style' => self::compileStyles(
                \array_merge(
                    [
                        $this->config['global']['styleBody'],
                        $hasForm ? $this->config['form']['styleBody'] : [],
                        $hasList ? $this->config['list']['styleBody'] : [],
                        $hasStat ? $this->config['stat']['styleBody'] : [],
                        $this->presenter->additionalStyles,
                    ],
                    $componentStyles)
            ),
            'script' => self::compileScripts(
                \array_merge(
                    [
                        $this->config['global']['script'],
                        $hasForm ? $this->config['form']['script'] : [],
                        $hasList ? $this->config['list']['script'] : [],
                        $hasStat ? $this->config['stat']['script'] : [],
                        $this->presenter->additionalScripts,
                        $module,
                        $presenter,
                        $action,
                    ],
                    $componentScripts)
            ),
        ];

        $this->cache->save($cacheName, $assets);
        return $assets;
    }

    private static function compileStyles(array $assets) : array
    {
        $return = [];

        foreach ($assets as $styles) {
            if (empty($styles)) {
                continue;
            }

            $files = new \WebLoader\FileCollection(\getcwd() . '/../');
            $files->addFiles($styles);

            $compiler = \Nepttune\AssetCompiler\Compiler::createCssCompiler($files, \getcwd() . '/webloader/');
            $compiler->setCheckLastModified(false);
            $compiler->setJoinFiles(true);
            $compiler->addFilter(new \WebLoader\Filter\ScssFilter());
            $compiler->addFilter(new \Nepttune\AssetFilter\CssMinFilter());

            $path = '/webloader/' . $compiler->generate()[0]->getFile();
            $return[$path] = self::generateChecksum($path);
        }

        return $return;
    }

    private static function compileScripts(array $assets) : array
    {
        $return = [];

        foreach ($assets as $scripts) {
            if (empty($scripts)) {
                continue;
            }

            $files = new \WebLoader\FileCollection(\getcwd() . '/../');
            $files->addFiles($scripts);

            $compiler = \Nepttune\AssetCompiler\Compiler::createJsCompiler($files, \getcwd() . '/webloader/');
            $compiler->setCheckLastModified(false);
            $compiler->setJoinFiles(true);
            $compiler->addFilter(new \Nepttune\AssetFilter\JsMinFilter());

            $path = '/webloader/' . $compiler->generate()[0]->getFile();
            $return[$path] = self::generateChecksum($path);
        }

        return $return;
    }
}
