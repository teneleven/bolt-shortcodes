<?php
namespace Bolt\Extension\Maiorano\BoltShortcodes;

use Bolt\BaseExtension;
use Bolt\Shortcodes\TwigShortcode;
use Maiorano\Shortcodes\Manager\ShortcodeManager;
use Symfony\Component\ClassLoader\Psr4ClassLoader;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Extension
 * @package Bolt\Extension\Maiorano\BoltShortcodes
 */
class Extension extends BaseExtension
{
    /**
     * @var ManagerInterface
     */
    private $manager;

    /**
     * @return string
     */
    public function getName()
    {
        return "bolt-shortcodes";
    }

    /**
     * @return void
     */
    public function initialize()
    {
        $themeBase = implode(DIRECTORY_SEPARATOR, [
            $this->app['resources']->getPath('themepath'),
            $this->config['base_directory']
        ]);
        $globalBase = implode(DIRECTORY_SEPARATOR, [
            $this->app['resources']->getPath('extensions'),
            $this->config['base_directory']
        ]);

        $this->setupAutoloader([
            'Bolt\\Shortcodes\\' => [
                'shortcodes.theme' => [$themeBase, $this->config['class_directory']],
                'shortcodes.global' => [$globalBase, $this->config['class_directory']],
                'shortcodes.default' => [__DIR__, 'src', 'Shortcodes']
            ],
            'Maiorano\\Shortcodes\\' => [
                'shortcodes.vendor' => [__DIR__, 'vendor', 'maiorano84', 'shortcodes', 'src']
            ]
        ]);
        $this->setupTwigLoader([
            'shortcodes.theme' => [$themeBase, $this->config['template_directory']],
            'shortcodes.global' => [$globalBase, $this->config['template_directory']]
        ]);

        $this->addTwigFilter('do_shortcode', 'doShortcode');
        $this->addTwigFunction('do_shortcode', 'doShortcode');
        $this->setupManager();
    }

    /**
     * @param $content
     * @param null $tags
     * @param bool $nested
     * @return \Twig_Markup
     */
    public function doShortcode($content, $tags = null, $nested = false)
    {
        if (!$this->isEnabled()) {
            return $content;
        }

        return new \Twig_Markup($this->manager->doShortcode($content, $tags, $nested), 'UTF-8');
    }

    /**
     * @param array $paths
     */
    private function setupAutoloader(array $paths = [])
    {
        require_once __DIR__ . '/src/ClassLoader/Psr4ClassLoader.php';

        $loader = new Psr4ClassLoader();
        foreach ($paths as $ns => $path) {
            foreach ($path as $k => $dir) {
                $loader->addPrefix($ns, implode(DIRECTORY_SEPARATOR, $dir));
            }
        }
        $loader->register();
    }

    /**
     * @param array $paths
     */
    private function setupTwigLoader(array $paths = [])
    {
        foreach ($paths as $ns => $path) {
            try {
                $this->app['twig.loader.filesystem']->addPath(implode(DIRECTORY_SEPARATOR, $path));
            } catch (\Twig_Error_Loader $e) {
                continue;
            }
        }
        $fallback = new \Twig_Loader_Array(array(
            '_bolt-shortcodes.twig' => '<div class="default-shortcodes">
                <p>Your shortcodes have been successfully parsed! Please add your templates as needed to any of the following directories:</p>
                <ul>
                    <li><strong>Theme Directory: </strong>{{ shortcode.paths.theme }}</li>
                    <li><strong>Global Directory: </strong>{{ shortcode.paths.global }}</li>
                </ul>
            </div>'
        ));
        $this->app['twig.loader']->addLoader($fallback);
        $this->app['twig']->addGlobal('shortcode', [
            'manager' => $this->manager,
            'paths' => [
                'theme' => $this->app['paths']['theme'] . $this->config['base_directory'] . '/' . $this->config['template_directory'],
                'global' => $this->app['paths']['extensions'] . $this->config['base_directory'] . '/' . $this->config['template_directory']
            ]
        ]);
    }

    /**
     * Disable the extension in live edit mode.
     *
     * @return bool if the extension is enabled
     */
    private function isEnabled()
    {
        // Get a request object, if not initialized by Silex yet, we'll create our own
        try {
            $request = $this->app['request'];
        } catch (\RuntimeException $e) {
            $request = Request::createFromGlobals();
        }

        // only enable if we're not in live edit mode
        return !((bool) $request->get('_live-editor-preview'));
    }

    /**
     * @throws \Maiorano\Shortcodes\Exceptions\RegisterException
     */
    private function setupManager()
    {
        $this->manager = new ShortcodeManager;
        foreach ($this->config['tags'] as $tag) {
            $shortcode = $this->buildShortcode($tag);
            $this->manager->register($shortcode);
        }
    }

    /**
     * @param array $tag
     * @return ShortcodeInterface
     */
    private function buildShortcode($tag)
    {
        if (isset($tag['class'])) {
            $reflection = new \ReflectionClass($tag['class']);
            if ($reflection->isSubclassOf('\\Bolt\\Shortcodes\\BaseShortcode')) {
                $shortcode = $reflection->newInstance($this->app);
                $shortcode->configure($tag, $this->config);
            } else {
                $shortcode = $reflection->newInstance();
            }
        } else {
            $atts = isset($tag['attributes']) ? $tag['attributes'] : [];
            $shortcode = new TwigShortcode($this->app, $tag['name'], $atts);
            $shortcode->configure($tag, $this->config);
        }

        return $shortcode;
    }
}
