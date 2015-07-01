<?php
/*
 * Copyright (c) 2015 Joshua "Andrew" O'Rourke
 */

namespace Core\Model\Field;

class Base
{
    protected $_model = null;
    protected $_value = null;
    protected $_columnName = null;
    protected $_required = false;
    protected $_dirty = false;
    
    public function __construct($model, $name, $parameters = null)
    {
        $this->_model = $model;
        if(isset($parameters['column']))
        {
            $this->_columnName = $parameters['column'];
        }
        else
        {
            $this->_columnName = $name;
        }
        if(isset($parameters['required']))
        {
            $this->_required = $parameters['required'] == true;
        }
    }
    public function cast($value)
    {
        return $value;
    }
    
    public function getValue()
    {
        return $this->_value;
    }
    
    public function setValue($value)
    {
        $this->_value = $this->cast($value);
    }
    
    public function getColumnName()
    {
        return $this->_columnName;
    }
    
    public function isRequired()
    {
        return $this->_required;
    }
    
    public function setDirty($dirty)
    {
        $this->_dirty = boolval($dirty);
    }
    
    public function isDirty()
    {
        return $this->_dirty;
    }
}