<?php

import('vanilla.util.StringMap');
import('vanilla.mail.MimeUtil');

class MimeParameterList
{
    private $list;

// -------------------------------------------------------------------------->

    public function __construct()
    {
	$this->list = new StringMap();
    }

// -------------------------------------------------------------------------->

    public function size()
    {
	return $this->list->size();
    }

// -------------------------------------------------------------------------->

    public function get($name)
    {
	return $this->list->get(strtolower($name));
    }

    public function set($name, $value)
    {
	if ( !empty($value) )
	{
	    return $this->list->put(strtolower(trim($name)), $value);
	}
    }

    public function remove($name)
    {
	return $this->list->remove(strtolower($name));
    }

// -------------------------------------------------------------------------->

    public function getLine($lineLength)
    {
	$s = '';

	foreach ( $this->list->keys() as $name )
	{
	    $value = self::quote( $this->list->get($name) );
	    $s = "$s; ";
	    $lineLength += 2;

	    $length = strlen($name) + strlen($value) + 1;

	    if ( $lineLength + $length > 76 )
	    {
		$s = "$s\r\n\t";
		$lineLength = 8;
	    }

	    $s = "$s$name=$value";
	}

	return $s;
    }

    public function __toString()
    {
	return $this->getLine(0);
    }

// -------------------------------------------------------------------------->

    private static function quote($s)
    {
	return MimeUtil::quote($s, MimeUtil::MIME_DELIMITERS);
    }
}
?>
