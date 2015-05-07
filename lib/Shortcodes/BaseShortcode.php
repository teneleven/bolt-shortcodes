<?php
namespace Bolt\Shortcodes;

use Maiorano\Shortcodes\Contracts\ShortcodeInterface;
use Maiorano\Shortcodes\Contracts\AttributeInterface;
use Maiorano\Shortcodes\Contracts\Traits;
use Bolt\Application;

abstract class BaseShortcode implements ShortcodeInterface, AttributeInterface{
    use Traits\Shortcode, Traits\Attribute;

    protected $app;
    protected $attributes = [];

    public function __construct(Application $app){
        $this->app = $app;
    }

}