<?php
namespace Bolt\Shortcodes;

use Maiorano\Shortcodes\Contracts\ShortcodeInterface;
use Maiorano\Shortcodes\Contracts\AttributeInterface;
use Maiorano\Shortcodes\Contracts\Traits;

abstract class BaseShortcode implements ShortcodeInterface, AttributeInterface{
    use Traits\Shortcode, Traits\Attribute;

    protected $attributes = [];
}