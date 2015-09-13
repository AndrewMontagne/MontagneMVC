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

    static public function route(array $path, $action, \stdClass $data, $thisClass = null)
    {
        $page = null;
        if(is_null($thisClass))
        {
            $thisClass = get_called_class();
        }

        if(count($path) > 0)
        {
            $nextPage = $thisClass . '\\' . ucfirst($path[0]);
            if(class_exists($nextPage))
            {
                array_shift($path);
                $page = $nextPage::route($path, $action, $data);
            }
            else
            {
                $page = null;
            }
        }
        else
        {
            $page = new $thisClass();
            $page->routingData = $data;

            if(is_null($action) || strlen($action) <= 0)
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

        if(is_null($page))
        {
            throw new \Core\Exception\HttpException(404, 'Could not find page ' . $nextPage);
        }

        return $page;
    }
}
