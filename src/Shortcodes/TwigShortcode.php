<?php
namespace Bolt\Shortcodes;

use Bolt\Application;

/**
 * Class TwigShortcode
 * @package Bolt\Shortcodes
 */
class TwigShortcode extends BaseShortcode
{
    /**
     * @var
     */
    protected $app;

    /**
     * @param Application $app
     * @param $name
     * @param $atts
     */
    public function __construct(Application $app, $name, $atts)
    {
        parent::__construct($app);
        $this->name = $name;
        $this->attributes = $atts;
    }
}
