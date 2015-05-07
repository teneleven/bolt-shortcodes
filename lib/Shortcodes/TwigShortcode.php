<?php
namespace Bolt\Shortcodes;

use Maiorano\Shortcodes\Library\SimpleShortcode;
use Bolt\Application;

class TwigShortcode extends SimpleShortcode{
    protected $app;
    protected $template;
    public function __construct(Application $app, array $config, $name, $atts){
        $this->app = $app;
        $this->template = $this->findTemplate($name, $config);
        parent::__construct($name, $atts, function($content=null, $atts=[]){
            return $this->app['render']->render($this->template, ['content'=>$content, 'attributes'=>$atts, 'tag'=>$this->name]);
        });
    }
    public function findTemplate($info, $config){
        $names = [
            isset($info['template_name']) ? $info['template_name'] : '',
            $config['default_prefix'].$this->name.'.twig',
            $config['default_template'],
            '_bolt-shortcodes.twig'
        ];

        foreach($names as $name){
            if($name && $this->app['twig.loader.filesystem']->exists($name)){
                break;
            }
        }
        $this->template = $name;
    }
}