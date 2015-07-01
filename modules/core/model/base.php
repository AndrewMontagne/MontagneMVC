<?php
/*
 * Copyright (c) 2015 Joshua "Andrew" O'Rourke
 */

namespace Core\Model;
use Exception;

class Base
{
    private $_fields = [];
    private $_exists = false;
    
    public static function isMutable()
    {
        return false;
    }

    public static function getModelDatabase()
    {
        return 'default';
    }
    
    public static function getModelPrimaryKey()
    {
        return 'id';
    }
    
    public static function getModelTable()
    {
        return null;
    }
    
    protected function addField($name, $type = 'string', $parameters = null)
    {
        $fieldType = 'Core\\Model\\Field\\' . ucfirst($type);
        
        if(class_exists($fieldType, true))
        {
            $this->_fields[$name] = new $fieldType($this, $name, $parameters);
        }
        else
        {
            throw new Exception($fieldType . ' is an incorrect field type');
        }
    }

    public function __construct($modelId = null)
    {
        if(!is_null($modelId))
        {
            $this->initWithId($modelId);
        }
    }
    
    private function initWithId($modelId)
    {
        if(is_null(static::getModelTable()))
        {
            throw new Exception(get_called_class() . ' does not have a database table set.');
        }

        $database = \Core\Database::get(static::getModelDatabase());
        $data = $database->query('SELECT * FROM ' . static::getModelTable() . ' WHERE ' . static::getModelPrimaryKey() . ' = :id',
                ['id'=>$modelId]);

        if(count($data) < 1)
        {
            throw new Exception('No ' . get_class($this) . ' found for id ' . $modelId);
        }

        $datum = $data[0];

        $this->populate($datum);
    }
    
    public function populate($datum)
    {
        foreach($this->_fields as $name => $field)
        {
            if(isset($datum[$field->getColumnName()]))
            {
                $value = $datum[$field->getColumnName()];
                $field->setValue($value);
            }
            else if($field->isRequired())
            {
                throw new Exception($name . ' is a required field!');
            }
        }
        $this->_exists = true;
    }
    
    public function __get($property)
    {
        return $this->_fields[$property]->getValue();
    }
    
    public function __set($property, $value)
    {
        if(static::isMutable())
        {
            $this->_fields[$property]->setValue($value);
            $this->_fields[$property]->setDirty(true);
        }
        else
        {
            throw new Exception('Cannot assign ' . $value . ' to ' . get_called_class() . 
            '->' . $property . ', model is immutable.');
        }
    }
    
    public function save()
    {
        if(!static::isMutable())
        {
            throw new Exception('Cannot save ' . get_called_class() . 
            ', model is immutable.');
        }
        
        $dirtyFields = [static::getModelPrimaryKey() => $this->getPrimaryKey()];
        $update = [];
        $allKeys = [];
        $allValues = [];
        
        foreach($this->_fields as $field)
        {
            if($field->isDirty())
            {
                $column = $field->getColumnName();
                $dirtyFields[$column] = $field->getValue();
                $allKeys[] = $column;
                $allValues[] = ':' . $column;
                $update[] = $column . ' = :' . $column;
                $field->setDirty(false);
            }
        }

        $database = \Core\Database::get(static::getModelDatabase());
        if($this->_exists)
        {
            $sql = 'UPDATE `' . static::getModelTable() .'` SET ' . implode(', ', $update) . ' WHERE ' . static::getModelPrimaryKey() . ' = :' . static::getModelPrimaryKey();
        }
        else
        {
            $sql = 'INSERT INTO `' . static::getModelTable() .'` (' . implode(', ', $allKeys) . ') VALUES (' . implode(', ', $allValues) . ')';
        }
        
        $new_id = $database->insert($sql, $dirtyFields);
        
        if(!$this->_exists)
        {
            $this->_exists = true;
            $this->initWithId($new_id);
        }
    }
    
    static public function getByForeignKey($foreignKey, $value)
    {
        return static::getWhere('WHERE `' . $foreignKey . '` = :value', ['value'=>$value]);
    }
    
    static public function getWhere($sql = '', $values = [])
    {
        $database = \Core\Database::get(static::getModelDatabase());
        $data = $database->query('SELECT * FROM `' . static::getModelTable() . '` ' . $sql, $values);
        
        $data = static::preProcessGetWhere($data);
        
        if(!is_array($data))
        {
            throw new Exception(get_called_class());
        }
        
        $result = [];
        
        foreach($data as $row)
        {
            $class = get_called_class();
            $model = new $class();
            $model->populate($row);
            $result[] = $model;
        }
        
        return $result;
    }
    
    static protected function preProcessGetWhere($data)
    {
        return $data;
    }


    public function getPrimaryKey()
    {
        return $this->__get(static::getModelPrimaryKey());
    }
    
    public function toStdClass()
    {
        $array = [];
        
        foreach($this->_fields as $field)
        {
            $array[$field->getColumnName()] = $field->getValue();
        }
        
        return (object)$array;
    }
    
    public function toJSON($pretty = false)
    {
        $json = $this->toStdClass();
        
        if($pretty)
        {
            return json_encode($json, JSON_PRETTY_PRINT);
        }
        else
        {
            return json_decode($json);
        }
    }
}