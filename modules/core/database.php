<?php
/*
 * Copyright (c) 2015 Joshua "Andrew" O'Rourke
 */

namespace Core;
use PDO;

class Database
{
    static private $connections = [];
    
    /**
     * Connects to a database defined in the configuration file in a lazy loading manner
     * 
     * @param string $database The configuration name of the database
     * @return \Core_Database
     */
    static public function get($database = 'default')
    {
        if(array_key_exists($database, self::$connections))
        {
            return self::$connections[$database];
        }
        else
        {
            $connection = new Database($database);
            self::$connections[$database] = $connection;
            return $connection;
        }
    }
    
    private $pdo = null;
    
    public function __construct($database)
    {
        global $CONFIG;
        
        $configEntry = $CONFIG['databases'][$database];
        $databaseInfo = $CONFIG[$configEntry];
        
        $this->pdo = new PDO($databaseInfo['url'], $databaseInfo['user'], $databaseInfo['password']);
    }
    
    public function query($query, $parameters = null, $returnValue = true)
    {
        $statement = $this->pdo->prepare($query);
        
        $this->bind($statement, $parameters);
        
        $success = $statement->execute();
        
        if(!$success)
        {
            $error = $statement->errorInfo();
            throw new Exception('Database Error: ' . json_encode($error));
        }
        
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function insert($query, $parameters = null)
    {
        $statement = $this->pdo->prepare($query);
        
        $this->bind($statement, $parameters);
        
        $success = $statement->execute();
        
        if(!$success)
        {
            $error = $statement->errorInfo();
            throw new Exception('Database Error: ' . $error[0]);
        }
        
        return $this->pdo->lastInsertId();
    }
    
    protected function bind(&$statement, $parameters)
    {
        if(!is_null($parameters))
        {
            foreach ($parameters as $parameter => $value) 
            {
                $parameter = ':' . $parameter;
                
                if(strpos($statement->queryString, $parameter) === false)
                {
                    continue;
                }

                if(is_int($value))
                {
                    $statement->bindValue($parameter, $value, PDO::PARAM_INT);
                }
                else if(is_float($value))
                {
                    $statement->bindValue($parameter, $value, PDO::PARAM_STR);
                }
                else if(is_null($value))
                {
                    $statement->bindValue($parameter, null, PDO::PARAM_NULL);
                }
                else if(is_a($value, 'DateTime'))
                {
                    $statement->bindValue($parameter, $value->format('Y-m-d H:i:s'), PDO::PARAM_STR);
                }
                else if(is_a($value, '\\Core\\Model\\Base'))
                {
                    $statement->bindValue($parameter, $value->getPrimaryKey(), PDO::PARAM_INT);
                }
                else
                {
                    $statement->bindValue($parameter, $value, PDO::PARAM_STR);
                }
            }
        }
    }
}
