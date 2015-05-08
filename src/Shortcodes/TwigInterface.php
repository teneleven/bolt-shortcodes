<?php
namespace Bolt\Shortcodes;

/**
 * Interface TwigInterface
 * @package Bolt\Shortcodes
 */
interface TwigInterface
{
    /**
     * @return mixed
     */
    public function getTemplate();

    /**
     * @param array $data
     * @return mixed
     */
    public function render(array $data = []);
}
