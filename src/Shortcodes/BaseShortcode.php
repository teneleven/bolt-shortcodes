<?php
namespace Bolt\Shortcodes;

use Maiorano\Shortcodes\Contracts\ShortcodeInterface;
use Maiorano\Shortcodes\Contracts\AttributeInterface;
use Maiorano\Shortcodes\Contracts\Traits;
use Bolt\Application;

/**
 * Class BaseShortcode
 * @package Bolt\Shortcodes
 */
abstract class BaseShortcode implements ShortcodeInterface, AttributeInterface, TwigInterface
{
    use Traits\Shortcode, Traits\Attribute, TwigTrait;

    /**
     * @var Application
     */
    protected $app;
    /**
     * @var mixed
     */
    protected $twig;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var array
     */
    protected $attributes = [];
    /**
     * @var string
     */
    protected $template;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->twig = $app['render'];
    }

    /**
     * @param null $content
     * @param array $atts
     * @return mixed
     */
    public function handle($content = null, $atts = [])
    {
        return $this->render(['content' => $content, 'attributes' => $atts, 'tag' => $this->name]);
    }

    /**
     * @param $info
     * @param $config
     * @return mixed
     */
    public function configure($info, $config)
    {
        if (isset($info['template_name'])) {
            if ($this->app['twig.loader.filesystem']->exists($info['template_name'])) {
                $this->template = $info['template_name'];

                return $info['template_name'];
            }
        }
        $names = [
            isset($info['template_name']) ? $info['template_name'] : '',
            $config['default_prefix'] . $this->name . '.twig',
            $config['default_template'],
            '_bolt-shortcodes.twig'
        ];

        foreach ($names as $name) {
            if ($name && $this->app['twig.loader.filesystem']->exists($name)) {
                break;
            }
        }
        $this->template = $name;
    }
}
