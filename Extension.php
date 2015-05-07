<?php
namespace Bolt\Extension\Maiorano\BoltShortcodes;

use Bolt\BaseExtension;
use Bolt\Shortcodes\TwigShortcode;
use Maiorano\Shortcodes\Manager\ShortcodeManager;
use Symfony\Component\ClassLoader\Psr4ClassLoader;

class Extension extends BaseExtension{
    private $manager;
    private $themeBase;
    private $globalBase;
    public function getName(){
        return "bolt-shortcodes";
    }
    public function initialize(){
        $this->themeBase = implode(DIRECTORY_SEPARATOR, [
            $this->app['resources']->getPath('templatespath'),
            $this->config['base_directory']
        ]);
        $this->globalBase = implode(DIRECTORY_SEPARATOR, [
            $this->app['resources']->getPath('extensions'),
            $this->config['base_directory']
        ]);

        $this->setupAutoloader([
            'Bolt\\Shortcodes\\'=>[
                'shortcodes.theme'=>[$this->themeBase, $this->config['class_directory']],
                'shortcodes.global'=>[$this->globalBase, $this->config['class_directory']],
                'shortcodes.default'=>[__DIR__, 'lib', 'Shortcodes']
            ],
            'Maiorano\\Shortcodes\\' => [
                'shortcodes.vendor'=> [__DIR__, 'vendor', 'maiorano84', 'shortcodes', 'src']
            ],
            'Bolt\\Extension\\Maiorano\\BoltShortcodes\\' => [
                'shortcodes.source' => [__DIR__, 'src']
            ]
        ]);
        $this->setupTwigLoader([
            'shortcodes.theme'=>[$this->themeBase, $this->config['template_directory']],
            'shortcodes.global'=>[$this->globalBase, $this->config['template_directory']]
        ]);
        $this->addTwigFilter('do_shortcode', 'doShortcode');
        $this->addTwigFunction('do_shortcode', 'doShortcode');
        $this->setupManager();
        $this->app['twig']->addGlobal('shortcode', [
            'manager'=>$this->manager,
            'paths'=>[
                'theme'=>$this->app['paths']['theme'].$this->config['base_directory'].'/'.$this->config['template_directory'],
                'global'=>$this->app['paths']['extensions'].$this->config['base_directory'].'/'.$this->config['template_directory']
            ]
        ]);
    }
    public function doShortcode($content, $tags=null, $nested=false){
        return new \Twig_Markup($this->manager->doShortcode($content, $tags, $nested), 'UTF-8');
    }
    private function setupAutoloader(array $paths = []){
        require_once __DIR__.'/lib/ClassLoader/Psr4ClassLoader.php';

        $loader = new Psr4ClassLoader();
        foreach($paths as $ns=>$path){
            foreach($path as $k=>$dir){
                $loader->addPrefix($ns, implode(DIRECTORY_SEPARATOR, $dir));
            }
        }
        $loader->register();
    }
    private function setupTwigLoader(array $paths = []){
        foreach($paths as $ns=>$path){
            try{
                $this->app['twig.loader.filesystem']->addPath(implode(DIRECTORY_SEPARATOR, $path));
            }
            catch(\Twig_Error_Loader $e){
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
    }
    private function setupManager(){
        $this->manager = new ShortcodeManager;
        foreach($this->config['tags'] as $tag){
            if(isset($tag['class'])){
                $shortcode = $this->buildShortcode($tag['class']);
            }
            else{

                $shortcode = $this->getDefaultShortcode($tag);
            }
            $this->manager->register($shortcode);
        }
    }
    private function buildShortcode($class){
        $reflection = new \ReflectionClass($class);
        if($reflection->isSubclassOf('\\Bolt\\Shortcodes\\BaseShortcode')){
            $shortcode = $reflection->newInstanceArgs($this->app);
        }
        else{
            $shortcode = $reflection->newInstance();
        }
        return $shortcode;
    }
    private function getDefaultShortcode($tag){
        $atts = isset($tag['attributes']) ? $tag['attributes'] : [];
        $default = new TwigShortcode($this->app, $this->config, $tag['name'], $atts);
        $default->findTemplate($tag, $this->config);
        return $default;
    }
}






