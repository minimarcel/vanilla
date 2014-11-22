<?php

import('vanilla.text.json.JSONPropertyBag');
import('vanilla.text.json.JSONSerializer');
import('vanilla.text.DateFormat');
import('vanilla.util.Date');

/**
 *  
 */
interface JSONPropertyValue
{
    public function serialize(); 
}
