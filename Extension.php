<?php
namespace Bolt\Extension\Maiorano\BoltShortcodes;

use Bolt\BaseExtension;
use Maiorano\Shortcodes\Manager\ShortcodeManager;
use Maiorano\Shortcodes\Library\SimpleShortcode;
use Symfony\Component\ClassLoader\Psr4ClassLoader;

class Extension extends BaseExtension{
    private $manager;
    public function getName(){
        return "shortcodes";
    }
    public function initialize(){
        $this->setupAutoloader();

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
    }
    public function doShortcode($content, $tags=null, $nested=false){
        return new \Twig_Markup($this->manager->doShortcode($content, $tags, $nested), 'UTF-8');
    }
    private function setupAutoloader(){
        require_once __DIR__.'/lib/ClassLoader/Psr4ClassLoader.php';
        $classPath = [
            $this->app['resources']->getPath('templatespath'),
            $this->config['base_directory'],
            $this->config['class_directory']
        ];
        $prefixes = [
            'Maiorano\\Shortcodes\\' => [__DIR__.'/vendor/maiorano84/shortcodes/src'],
            'Bolt\\Shortcodes\\' => [__DIR__.'/src', implode('/', $classPath)]
        ];

        $loader = new Psr4ClassLoader();
        foreach($prefixes as $ns=>$paths){
            foreach($paths as $path){
                $loader->addPrefix($ns, $path);
            }
        }
        $loader->register();
    }
    private function getDefaultShortcode($name, $atts=[]){
        $twig = $this->app['render'];
        $template = $this->getTemplatePath($name);
        return new SimpleShortcode($name, $atts, function($content=null, $atts=[]) use ($twig, $template){
            return $twig->render($template, ['content'=>$content, 'atts'=>$atts]);
        });
    }
    private function getTemplatePath($name){
        $parts = [
            $this->config['base_directory'],
            $this->config['template_directory']
        ];
        $templatePath = implode('/', $parts);
        $prefix = $this->config['template_prefix'];

        $target = "{$templatePath}/{$prefix}{$name}.twig";
        $exists = file_exists($this->app['resources']->getPath('templatespath')."/{$target}");
        return $exists ? $target : $templatePath."/".$this->config['default_template'];
    }
}






