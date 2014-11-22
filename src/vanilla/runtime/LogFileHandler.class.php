<?php

import('vanilla.runtime.LogHandler');
import('vanilla.io.FileWriter');

/**
 * 
 */
class LogFileHandler extends LogHandler
{
    protected $writer;

//----------------------------------------------->

    public function __construct(File $file)
    {
	parent::__construct();
	$this->writer = new FileWriter($file, true);
    } 

    function __destruct()
    {
	if ( !empty($this->writer) )
	{
	    try
	    {
		$this->writer->close();
	    }
	    catch(Exception $e)
	    {}
	}
    }

//----------------------------------------------->

    public function doPublish(LogRecord $record)
    {
	$this->writer->writeln("$record");
    }
}
?>
