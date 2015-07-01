<?php
/*
 * Copyright (c) 2015 Joshua "Andrew" O'Rourke
 */

namespace Core\Model\Field;
use Exception;

class Model extends Integer
{
    private $_modelClass = null;
    private $_modelInstance = null;
    
    public function __construct($model, $name = null, $parameters = null)
    {
        if(!isset($parameters['model']))
        {
            throw new Exception('Model not set for parameter');
        }
        
        $this->_modelClass = $parameters['model'];
        
        if(!is_subclass_of($this->_modelClass, '\\Core\\Model\\Base', true))
        {
            throw new Exception($this->_modelClass . ' is not a valid Model');
        }
        
        parent::__construct($model, $name, $parameters);
    }
    
    public function getValue()
    {
        if($this->_modelInstance == null)
        {
            $this->_modelInstance = new $this->_modelClass($this->_value);
        }
        return $this->_modelInstance;
    }
    
    public function setValue($value)
    {
        $this->_value = $this->cast($value);
        $this->_modelInstance = null;
    }
    
    public function cast($value)
    {
        if(is_a($value, '\\Core\\Model\\Base', true))
        {
            return parent::cast($value->getPrimaryKey());
        }
        else
        {
            return parent::cast($value);
        }
    }
}