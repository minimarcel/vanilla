<?php

import('vanilla.io.IOException');
import('vanilla.io.StreamWriter');
import('vanilla.io.StreamReader');

class Socket
{
    private $target; 
    private $port; 

    private $stream;
    private $reader;
    private $writer;
    private $timeout;

    private $opened;
    private $closed;

// -------------------------------------------------------------------------->
    
    public function __construct($target, $port, $timeout=0)
    {
	$this->port	= $port;
	$this->target	= $target;
	$this->closed	= false;
	$this->opened	= false;
	$this->timeout	= $timeout;
    }

    public function __destruct()
    {
	try
	{
	    $this->close();
	}
	catch(Exception $e)
	{}
    }

// -------------------------------------------------------------------------->

    public function getTarget()
    {
	return $this->target;
    }

    public function getPort()
    {
	return $this->port;
    }

    public function getReader()
    {
	$this->checkValidity();
	return $this->reader;
    }

    public function getWriter()
    {
	$this->checkValidity();
	return $this->writer;
    }

// -------------------------------------------------------------------------->

    private function open()
    {
	$this->stream = fsockopen($this->target, $this->port, $this->timeout);	
	if ( empty($this->stream) )
	{
	    throw new IOException("Impossible d'établir une connection ($errno) : $errstr");
	}

	$this->opened = true;
	$this->writer = new StreamWriter($this->stream);
	$this->reader = new StreamReader($this->stream);
    }

    private function checkValidity()
    {
	if ( $this->closed )
	{
	    throw new IOException("Socket closed");
	}

	if ( !$this->opened )
	{
	    $this->open();
	}
    }

// -------------------------------------------------------------------------->

    public function close()
    {
	if ( $this->closed || !$this->opened )
	{
	    return;
	}

	$this->closed = true;
	$this->opened = false;

	if ( !empty($this->stream) )
	{
	    $this->writer->close();
	    $this->reader->close();

	    try
	    {
		fclose($this->stream);
	    }
	    catch(Exception $e)
	    {}
	}
    }
}
?>
