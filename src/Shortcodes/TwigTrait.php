<?php
namespace Bolt\Shortcodes;

trait TwigTrait {
    public function getTemplate(){
        return $this->template;
    }
    public function render(array $data=[]){
        return $this->twig->render($this->template, $data);
    }
}