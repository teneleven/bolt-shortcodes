<?php
namespace Bolt\Shortcodes;

/**
 * Class TwigTrait
 * @package Bolt\Shortcodes
 */
trait TwigTrait
{
    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function render(array $data = [])
    {
        return $this->twig->render($this->template, $data);
    }
}
