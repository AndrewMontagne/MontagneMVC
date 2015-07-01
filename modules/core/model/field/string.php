<?php
/*
 * Copyright (c) 2015 Joshua "Andrew" O'Rourke
 */

namespace Core\Model\Field;

class String extends Base
{
    public function validate($value)
    {
        return is_string($value);
    }
}