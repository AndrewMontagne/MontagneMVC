<?php
/*
 * Copyright (c) 2015 Joshua "Andrew" O'Rourke
 */

namespace Core\Model\Field;

class Boolean extends Base
{
    public function cast($value)
    {
        return boolval($value);
    }
}