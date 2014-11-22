<?php

import('vanilla.io.FileReader');
import('vanilla.localisation.parser.LocaleParserHandler');

/**
 *
 */
class LocaleParser
{
    const START		    = 0;
    const REDIRECT	    = 1;
    const LOCALE	    = 2;
    const PROPERTIES	    = 3;
    const STOP		    = 4;

    const REDIRECT_CHAR	    = '>';
    const LOCALE_CHAR	    = '$';
    const COLLECTION_CHAR   = '*';
    const PROPERTY_CHAR	    = '"';
    const COMMENT_CHAR	    = '#';

//  ---------------------------------------->

    private $state;
    private $handler;

//  ---------------------------------------->

    public function parse(File $file, LocaleParserHandler $handler)
    {
	$this->handler	    = $handler;
	$reader		    = new FileReader($file);

	try
	{
	    $this->state = self::START;
	    while ( ($line = $reader->readln()) !== false )
	    {
		$this->handleEvent($line);
	    }

	    if ( $this->state >= self::LOCALE )
	    {
		$this->state = self::STOP;
		$this->handler->finish();
	    }
	}
	catch(Exception $e)
	{
	    $this->close($reader);
	    throw $e;
	}

	$this->close($reader);
    }

    private function close($reader)
    {
	if ( isset($reader) )
	{
	    $reader->close();
	}
    }

//  ---------------------------------------->

    private function handleEvent($line)
    {
	$c = $line[0];
	if ( $line[1] != ' ' )
	{
	    throw new Exception("Missing space after char event"); 
	}

	$line = substr($line, 2); 

	switch ( $c )
	{
	    case self::COMMENT_CHAR	    : return $this->commentEvent($line);
	    case self::REDIRECT_CHAR	    : return $this->redirectEvent($line);
	    case self::LOCALE_CHAR	    : return $this->localeEvent($line);
	    case self::COLLECTION_CHAR	    : return $this->collectionEvent($line);
	    case self::PROPERTY_CHAR	    : return $this->propertyEvent($line);
	}

	throw new Exception("Unknown event char : $c");
    }

    private function commentEvent($line)
    {
	$this->handler->comment($line);
    }

    private function redirectEvent($line)
    {
	if ( $this->state != self::START )
	{
	    throw new Exception("Invalid state");
	}

	$this->state = self::REDIRECT;
	$this->handler->redirectTo($line);
    }

    private function localeEvent($line)
    {
	if ( $this->state != self::START )
	{
	    throw new Exception("Invalid state");
	}

	$this->state = self::LOCALE;
	$this->handler->startLocale($line);
    }

    private function collectionEvent($line)
    {
	if ( $this->state < self::LOCALE )
	{
	    throw new Exception("Invalid state");
	}

	$this->state = self::PROPERTIES;
	$this->handler->startPropertyCollection($line);
    }

    private function propertyEvent($line)
    {
	if ( $this->state != self::PROPERTIES )
	{
	    throw new Exception("Invalid state");
	}

	$this->handler->addProperty($line);
    }
}
?>
