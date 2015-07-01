<?php
/*
 * Copyright (c) 2015 Joshua "Andrew" O'Rourke
 */

namespace Core\Model;

class Error extends Base
{
    public static function getModelTable()
    {
        return 'errors';
    }
    
    public static function isMutable()
    {
        return true;
    }
    
    public function __construct($modelId = null)
    {
        /* @var int id */
        $this->addField('id', 'integer', ['required'=>true]);
        /* @property DateTime timestamp */
        $this->addField('timestamp', 'string', ['required'=>true]);
        /* @property string type */
        $this->addField('type', 'string', ['required'=>true]);
        /* /@property string level */
        $this->addField('level', 'string', ['required'=>true]);
        /* @property string message */
        $this->addField('message', 'string', ['required'=>true]);
        /* @property string file */
        $this->addField('file', 'string', ['required'=>true]);
        /* @property string line */
        $this->addField('line', 'integer', ['required'=>true]);
        /* @property string variables */
        $this->addField('variables', 'string', ['required'=>true]);
        /* @property string stack */
        $this->addField('stack', 'string', ['required'=>true]);
        /* @property string hash */
        $this->addField('hash', 'string', ['required'=>true]);
        
        parent::__construct($modelId);
    }
}