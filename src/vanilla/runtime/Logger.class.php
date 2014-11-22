<?php

import('vanilla.util.StringMap');
import('vanilla.util.ArrayList');
import('vanilla.runtime.LogHandler');
import('vanilla.runtime.LogFileHandler');
import('vanilla.runtime.LogRecord');
import('vanilla.runtime.HumanReadableException');

/**
 * 
 */
class Logger
{
    const LOGGER_SEVERE_LEVEL	= 4;
    const LOGGER_WARNING_LEVEL	= 3;
    const LOGGER_INFO_LEVEL	= 2;
    const LOGGER_CONFIG_LEVEL	= 1;
    const LOGGER_ALL_LEVEL	= 0;

//----------------------------------------------->

    public static $DoNotLogHumanReadableExceptions = true;

//----------------------------------------------->

    protected static $loggers;
    protected static $defaultLogger;

//----------------------------------------------->

    protected $name;
    protected $handlers;
    protected $level;

//----------------------------------------------->

    protected function __construct($name)
    {
	$this->name	= $name;
	$this->handlers	= new ArrayList(); 
	$this->level	= self::LOGGER_ALL_LEVEL;
    } 

//----------------------------------------------->

    public function logRecord(LogRecord $record)
    {
	if ( $this->handlers->isEmpty() )
	{
	    echo "$record\n";
	}
	else 
	{
	    foreach ( $this->handlers->elements as $handler )
	    {
		$handler->publish($record);
	    }
	}
    }

    public function log($level, $msg, $e=null)
    {
	if ( self::$DoNotLogHumanReadableExceptions && ($e instanceof HumanReadableException) )
	{
	    return;
	}

	if ( $level < $this->level )
	{
	    return;
	}

	$record = new LogRecord($level, $msg);
	if ( !empty($e) )
	{
	    $record->setException($e);
	}

	$this->logRecord($record);
    }

    public function severe($msg, $e=null)
    {
	$this->log(self::LOGGER_SEVERE_LEVEL, $msg, $e);
    }

    public function warning($msg, $e=null)
    {
	$this->log(self::LOGGER_WARNING_LEVEL, $msg, $e);
    }

    public function info($msg, $e=null)
    {
	$this->log(self::LOGGER_INFO_LEVEL, $msg, $e);
    }

    public function config($msg, $e=null)
    {
	$this->log(self::LOGGER_CONFIG_LEVEL, $msg, $e);
    }

//----------------------------------------------->

    public function addHandler(LogHandler $handler)
    {
	$this->handlers->add($handler);
    }

    public function addFileHandler(File $file)
    {
	$handler = new LogFileHandler($file);
	$this->addHandler($handler);
	return $handler;
    }

    public function setLevel($level)
    {
	if ( $level < self::LOGGER_ALL_LEVEL || $level > self::LOGGER_SEVERE_LEVEL )
	{
	    return;
	}

	$this->level = $level;
    }

    public function getLevel()
    {
	return $this->level;
    }

//----------------------------------------------->

    public static function getDefault()
    {
	if ( empty(self::$defaultLogger) )
	{
	    self::$defaultLogger = self::getLogger('default');	
	}

	return self::$defaultLogger;
    }

    /**
     * Retourne un logger pour le nom donné.
     * Si ce logger n'existe pas, un logger est créé.
     */
    public static function getLogger($name)
    {
	if ( empty(self::$loggers) )
	{
	    self::$loggers = new StringMap();
	}

	$logger = self::$loggers->get($name);
	if ( empty($logger) )
	{
	    $logger = new Logger($name);
	    self::$loggers->put($name, $logger);
	}

	return $logger;
    }

    public static function levelToString($level)
    {
	switch($level)
	{
	    case self::LOGGER_SEVERE_LEVEL  : return "SEVERE";
	    case self::LOGGER_WARNING_LEVEL : return "WARNING";
	    case self::LOGGER_INFO_LEVEL    : return "INFO";
	    case self::LOGGER_CONFIG_LEVEL  : return "CONFIG";
	}

	return "UNKNOWN";
    }
}

// On force la création du logger par défaut
Logger::getDefault();
?>
