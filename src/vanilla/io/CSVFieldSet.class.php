<?php

import('vanilla.io.CSVReader');
import('vanilla.io.IOException');
import('vanilla.util.ArrayList');

class CSVFieldSet 
{
    private $lineNumber;
    private $reader;
    private $header;
    private $current;
    private $dateTimeFormat;

//  ------------------------------------------------------>

    public function __construct(CSVReader $reader, $hasHeader=true)
    {
        $this->reader 		= $reader;
        $this->lineNumber	= 0;

	if ( $hasHeader )
	{
	    $this->header 	= $reader->readFields();
	    $this->lineNumber	= 1;

	    if ( empty($this->header) )
	    {
		throw new IOException('No header found');
	    }
	}

        // TODO dateTimeFormat  = new SimpleDateFormat(DATE_TIME_FORMAT);
    }

    public function next()
    {
	$this->current = $this->reader->readFields();
	if ( empty($this->current) )
	{
	    return false;
	}
	
	$this->lineNumber++;
	return true;
    }

    public function getLineNumber()
    {
	return $this->lineNumber;
    }

//  ------------------------------------------------------>

    public function getLineSize()
    {
	if ( empty($this->current) )
	{
	    throw new IOException("End of stream reached or no cursor setted.");
	}

	return sizeof($this->current);
    }

    public function getString($colIndex)
    {
	if ( empty($this->current) )
	{
	    throw new IOException("End of stream reached or no cursor setted.");
	}

	$l = sizeof($this->current) - 1;

	if ( $colIndex < 0 )
	{
	    throw new IOException("Column index out of bounds $colIndex < 0");
	}
	else if ( $colIndex > $l )
	{
	    throw new IOException("Column index out of bounds : $colIndex > $l");
	}

	return trim($this->current[$colIndex]);
    }

    public function getStringByName($colName)
    {
	return $this->getString( $this->indexOf($colName) );
    }

    private function indexOf($colName) 
    {
	if ( empty($this->header) )
	{
	    throw new IOException('No header defined');
	}

	for ( $i = 0 ; $i < sizeof($this->header) ; $i++ )
	{
	    if ( $colName === $this->header[$i] )
	    {
		return $i;
	    }
	}

	throw new IOException("Unknown column named : $colName");
    }
}
