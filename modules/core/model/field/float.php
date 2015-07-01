<?php
/*
 * Copyright (c) 2015 Joshua "Andrew" O'Rourke
 */

namespace Core\Model\Field;

class Float extends Base
{
    public function cast($value)
    {
        return floatval($value);
    }
}