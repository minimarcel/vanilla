<?php

import('vanilla.text.plist.PListDictionary');
import('vanilla.text.plist.PListArray');
import('vanilla.text.plist.PListPrimitive');

class PListHelper 
{
    public static function toProperty($value)
    {
	if ( $value instanceof PListProperty )
	{
	    return $value;
	}

	if ( $value instanceof ArrayList || is_array($value) )
	{
	    $p = new PListArray();
	    $p->addAll($value);

	    return $p;
	}

	return new PListPrimitive($value);
    }
}
