<?php

import('vanilla.io.Writer');
import('vanilla.io.IOException');

class StringReader implements TextReader/*, SeekReader*/
{
    private $buffer = null;
    private $pos;
    private $len;

// -------------------------------------------------------------------------->

    public function __construct($buffer)
    {
	$this->buffer = $buffer;
	$this->pos = 0;
	$this->len = mb_strlen($buffer);
    }

// -------------------------------------------------------------------------->

    public function read($length)
    {
	$this->checkClosed();

	if ( $this->pos == $this->len )
	{
	    return false;
	}

	$length = max(0, $length);
	$length = min($this->len - $this->pos, $length);

	$s = mb_substr($this->buffer, $this->pos, $length);

	$this->pos += $length;

	return $s;
    }

    public function readln($length=-1)
    {
	$this->checkClosed();

	if ( $this->pos == $this->len )
	{
	    return false;
	}

	// on récupère la position du délimiteur de fin de chaîne
	$pos1 = mb_strpos($this->buffer, "\r", $this->pos);
	$pos2 = mb_strpos($this->buffer, "\n", $this->pos);

	$pos = min($pos1, $pos2);
	$len = $pos - $this->pos;

	if ( $length > -1 )
	{
	    $len = min($len, $length);
	}

	$s = mb_substr($this->buffer, $this->pos, $len);
	$this->pos += $len;

	if ( $this->buffer[$this->pos] == "\r" )
	{
	    $this->pos++;
	}

	if ( $this->buffer[$this->pos] == "\n" )
	{
	    $this->pos++;
	}

	return $s;
    }

    public function close()
    {
	$this->buffer = null;
    }

    public function seekTo($pos)
    {
	$this->checkClosed();

	$pos = max(0, $pos);
	$this->pos = min($pos, $this->len);
    }

    public function getSize()
    {
	$this->checkClosed();
	return $this->len;
    } 

    public function getCurrentPosition()
    {
	$this->checkClosed();
	return $this->pos;
    }

    public function readToEnd()
    {
	return $this->read($this->len - $this->pos);
    }

    public function readTextToEnd()
    {
	return $this->readToEnd();
    }

// -------------------------------------------------------------------------->

    private function checkClosed()
    {
	if ( $this->buffer == null )
	{
	    throw new IOException("Reader closed");
	}
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
