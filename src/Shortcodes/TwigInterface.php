<?php
namespace Bolt\Shortcodes;

interface TwigInterface {
    public function getTemplate();
    public function render(array $data=[]);
}