<?php
namespace Bolt\Shortcodes;

use Bolt\Application;

class TwigShortcode extends BaseShortcode{
    protected $app;
    public function __construct(Application $app, $name, $atts){
        parent::__construct($app);
        $this->name = $name;
        $this->attributes = $atts;
    }
}