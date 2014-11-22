<?php

import('vanilla.runtime.LogRecord');

/**
 * 
 */
abstract class LogHandler
{
    protected $level;

//----------------------------------------------->

    public function __construct()
    {
	$this->level = Logger::LOGGER_ALL_LEVEL;
    }

//----------------------------------------------->

    public function getLevel()
    {
	return $this->level;
    }

    public function setLevel($level)
    {
	$this->level = $level;
    }

//----------------------------------------------->

    public function publish(LogRecord $record)
    {
	if ( $this->level <= $record->getLevel() )
	{
	    $this->doPublish($record);
	}
    }

    protected abstract function doPublish(LogRecord $record);
}
?>
