<?php
namespace Bolt\Extension\Maiorano\BoltShortcodes;

use Bolt\BaseExtension;
use Maiorano\Shortcodes\Manager\ShortcodeManager;
use Maiorano\Shortcodes\Library\SimpleShortcode;
use Symfony\Component\ClassLoader\Psr4ClassLoader;

class Extension extends BaseExtension{
    private $manager;
    public function getName(){
        return "bolt-shortcodes";
    }
    public function initialize(){
        $this->setupAutoloader([
            'Bolt\\Shortcodes\\'=>[
                'shortcodes.theme'=>[
                    $this->app['resources']->getPath('templatespath'),
                    $this->config['base_directory'],
                    $this->config['class_directory']
                ],
                'shortcodes.global'=>[
                    $this->app['resources']->getPath('extensions'),
                    $this->config['base_directory'],
                    $this->config['class_directory']
                ],
                'shortcodes.default'=>[
                    __DIR__,
                    'src'
                ]
            ],
            'Maiorano\\Shortcodes\\' => [
                'shortcodes.vendor'=> [
                    __DIR__,
                    'vendor',
                    'maiorano84',
                    'shortcodes',
                    'src'
                ]
            ]
        ]);
        $this->setupTwigLoader([
            'shortcodes.theme'=>[
                $this->app['resources']->getPath('templatespath'),
                $this->config['base_directory'],
                $this->config['template_directory']
            ],
            'shortcodes.global'=>[
                $this->app['resources']->getPath('extensions'),
                $this->config['base_directory'],
                $this->config['template_directory']
            ]
        ]);
        $this->addTwigFilter('do_shortcode', 'doShortcode');
        $this->addTwigFunction('do_shortcode', 'doShortcode');

        $this->manager = new ShortcodeManager;
        foreach($this->config['tags'] as $tag){
            if(isset($tag['class'])){
                $reflection = new \ReflectionClass($tag['class']);
                $shortcode = $reflection->newInstance($this->app);
            }
            else{
                $atts = isset($tag['attributes']) ? $tag['attributes'] : [];
                $shortcode = $this->getDefaultShortcode($tag['name'], $atts);
            }
            $this->manager->register($shortcode);
        }
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
                <p>Your shortcodes have been successfully parsed! Please add your templates as needed</p>
            </div>'
        ));
        $this->app['twig.loader']->addLoader($fallback);
    }
    private function getDefaultShortcode($name, $atts=[]){
        $twig = $this->app['render'];
        $template = $this->getTemplate($name);
        return new SimpleShortcode($name, $atts, function($content=null, $atts=[]) use ($twig, $template){
            return $twig->render($template, ['content'=>$content, 'attributes'=>$atts]);
        });
    }
    private function getTemplate($name){
        $names = [
            $this->config['template_prefix'].$name.'.twig',
            $this->config['default_template']
        ];
        for($i=0, $max=count($names);$i<$max;$i++){
            if($this->app['twig.loader.filesystem']->exists($names[$i])){
                return $names[$i];
            }
        }
        return '_bolt-shortcodes.twig';
    }
}






