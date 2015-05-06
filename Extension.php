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
        $this->setupAutoloader();
        $this->setupTemplatePaths();

        $this->manager = new ShortcodeManager;
        foreach($this->config['tags'] as $tag){
            if(isset($tag['class'])){
                $shortcode = new $tag['class'];
            }
            else{
                $shortcode = $this->getDefaultShortcode($tag['name'], isset($tag['atts']) ? $tag['atts'] : []);
            }
            $this->manager->register($shortcode);
        }
        $this->addTwigFilter('do_shortcode', 'doShortcode');
        $this->addTwigFunction('do_shortcode', 'doShortcode');
    }
    public function doShortcode($content, $tags=null, $nested=false){
        return new \Twig_Markup($this->manager->doShortcode($content, $tags, $nested), 'UTF-8');
    }
    private function setupAutoloader(){
        require_once __DIR__.'/lib/ClassLoader/Psr4ClassLoader.php';

        $paths = [
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

        ];

        $loader = new Psr4ClassLoader();
        foreach($paths as $ns=>$path){
            foreach($path as $k=>$dir){
                $loader->addPrefix($ns, implode(DIRECTORY_SEPARATOR, $dir));
            }
        }
        $loader->register();
    }
    private function setupTemplatePaths(){
        $paths = [
            'shortcodes.theme'=>[
                $this->app['resources']->getPath('templatespath'),
                $this->config['base_directory'],
                $this->config['template_directory']
            ],
            'shortcodes.global'=>[
                $this->app['resources']->getPath('extensions'),
                $this->config['base_directory'],
                $this->config['template_directory']
            ],
            'shortcodes.default'=>[
                __DIR__,
                'templates'
            ]
        ];
        foreach($paths as $ns=>$path){
            try{
                $this->app['twig.loader.filesystem']->addPath(implode(DIRECTORY_SEPARATOR, $path));
            }
            catch(\Twig_Error_Loader $e){
                continue;
            }
        }
    }
    private function getDefaultShortcode($name, $atts=[]){
        $twig = $this->app['render'];
        $template = $this->getTemplate($name);
        return new SimpleShortcode($name, $atts, function($content=null, $atts=[]) use ($twig, $template){
            return $twig->render($template, ['content'=>$content, 'atts'=>$atts]);
        });
    }
    private function getTemplate($name){
        $names = [
            $this->config['template_prefix'].$name.'.twig',
            $this->config['default_template'],
            '_shortcodes.twig'
        ];
        for($i=0, $max=count($names);$i<$max;$i++){
            if($this->app['twig.loader.filesystem']->exists($names[$i])){
                break;
            }
        }
        return $names[$i];
    }
}






