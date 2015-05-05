<?php
namespace Bolt\Shortcodes;

use Maiorano\Shortcodes\Contracts\ShortcodeInterface;
use Maiorano\Shortcodes\Contracts\Traits\Shortcode;

abstract class BaseShortcode implements ShortcodeInterface{
    use Shortcode;
}