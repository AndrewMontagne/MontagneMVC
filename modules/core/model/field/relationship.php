<?php
/*
 * Copyright (c) 2015 Joshua "Andrew" O'Rourke
 */

namespace Core\Model\Field;
use Exception;

class Relationship extends Integer
{
    private $_modelClass = null;
    private $_relatives = null;
    private $_foreignKey = null;
    
    public function __construct($model, $name = null, $parameters = null)
    {
        if(!isset($parameters['model']))
        {
            throw new Exception('Model not set for parameter');
        }
        if(!isset($parameters['foreign']))
        {
            throw new Exception('Foreign key not set for parameter');
        }
        
        
        $this->_modelClass = $parameters['model'];
        $this->_foreignKey = $parameters['foreign'];
        
        if(!is_subclass_of($this->_modelClass, '\\Core\\Model\\Base', true))
        {
            throw new Exception($this->_modelClass . ' is not a valid Model');
        }
        
        parent::__construct($model, $name, $parameters);
    }
    
    public function validate($value)
    {
        return is_int($value) && $value >= 0;
    }
    
    public function getValue()
    {
        if($this->_relatives == null)
        {
            $this->_relatives = call_user_func($this->_modelClass . '::getByForeignKey', $this->_foreignKey, $this->_value);
        }
        return $this->_relatives;
    }
    
    public function setValue($value)
    {
        $this->_value = $this->cast($value);
        $this->_relatives = null;
    }
}