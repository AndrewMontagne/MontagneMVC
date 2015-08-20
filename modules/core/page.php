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
        throw new \Core\Exception\HttpException(404);
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
    
    static public function route(array $path, string $action)
    {
        if(count($path) > 0)
        {
            $nextPage = get_called_class() . '\\' . ucfirst($path[0]);
            
            if(class_exists($nextPage))
            {
                array_shift($path);
                $nextPage::route($path, $action);
            }
            else
            {
                throw new \Core\Exception\HttpException(404, 'Could not find page ' . $path);
            }
        }
        else
        {
            $thisClass = get_called_class();
            $page = new $thisClass;
            if(is_null($action) || strlen($action))
            {
                $page->indexAction();
            }
            else
            {
                if(method_exists($page, $action . 'Action'))
                {
                    $action .= 'Action';
                    $page->$action();
                }
                else
                {
                    $page->defaultAction($action);
                }
            }
        }
    }
}