<?php
/*
 * Copyright (c) 2015 Joshua "Andrew" O'Rourke
 */

namespace Core\Model\Field;

class Integer extends Base
{
    public function cast($value)
    {
        return intval($value);
    }
}