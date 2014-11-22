<?php

import('vanilla.util.Date');

/**
 * 
 */
class LogRecord
{
    protected $level;
    protected $message;
    protected $time;
    protected $exception;

//----------------------------------------------->

    public function __construct($level, $msg)
    {
	$this->level	= $level;
	$this->time	= new Date();
	$this->setMessage($msg);
    } 

//----------------------------------------------->

    public function setLevel($level)
    {
	$this->level = $level;
    }

    public function getLevel()
    {
	return $this->level;
    }

    public function setMessage($message)
    {
	$this->message = toString($message);
    }

    public function getMessage()
    {
	return $this->message;
    }

    public function setException(Exception $exception)
    {
	$this->exception = $exception;
    }

    public function getException()
    {
	return $this->exception;
    }

    public function setTime(Date $time)
    {
	$this->time = $time;
    }

    public function getTime()
    {
	return $this->time;
    }

//----------------------------------------------->

    /**
     * Retourne une représentation par défaut de ce log record
     */
    public function __toString()
    {
	$s = "" . Logger::levelToString($this->level) . " : " . $this->time . " : " . $this->message;
	if ( !empty($this->exception) )
	{
	    $s = $s . "\n(".get_class($this->exception).") ". $this->exception;
	    //$s = $s . "\n(Stack Trace)\n" . $this->exception->getTraceAsString();
	}

	return $s;
    }
}
?>
