<?php
/*
 * Copyright (c) 2015 Joshua "Andrew" O'Rourke
 */

namespace Core;

class Page
{
    public $title = 'Page';
    public $view = 'generic_message';

    public function indexAction()
    {
        $this->defaultAction('');
    }

    public function defaultAction($parameter)
    {
        throw new \Core\Exception\Http(404);
    }
    
    public function getView()
    {
        return './includes/views/' . $this->view . '.phtml';
    }
    
    public function finalise()
    {
        //
    }
    
    public function render()
    {
        //include($this->getView());
    }
}