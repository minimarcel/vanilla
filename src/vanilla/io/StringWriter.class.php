<?php

import('vanilla.io.TextWriter');
import('vanilla.io.IOException');

class StringWriter implements TextWriter
{
    private $buffer;
    private $closed = true;

// -------------------------------------------------------------------------->

    public function __construct()
    {
	$this->buffer = '';
	$this->closed = false;
    }

// -------------------------------------------------------------------------->

    public function write($s, $length=-1)
    {
	if ( $this->closed )
	{
	    throw new IOException("Writer closed");
	}

	if ( mb_strlen($s) == 0 )
	{
	    return;
	}

	$l = ($length >= 0 ? min(mb_strlen($s), $length) : mb_strlen($s));
	$this->buffer = $this->buffer . mb_substr($s, 0, $l);
    }

    public function writeText($s, $length=-1)
    {
	$this->write($s, $length);
    }

    public function close()
    {
	$this->closed = true;
    }

    public function clear()
    {
	if ( $this->closed )
	{
	    throw new IOException("Writer closed");
	}

	$this->buffer = '';
    }

    public function isClosed()
    {
	return $this->closed;
    }

// -------------------------------------------------------------------------->
    
    public function __toString()
    {
	return $this->getBuffer();
    }

    public function getBuffer()
    {
	return $this->buffer;
    }
}
?>
