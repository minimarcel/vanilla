<?php

import('vanilla.mail.MimeHeader');
import('vanilla.util.ArrayList');

class MimePartHeaders
{
    private $headers;

// -------------------------------------------------------------------------->

    public function __construct()
    {
	$this->headers = new StringMap();

	// on définit des headers par défaut pour les garder dans l'ordre
	$this->append("Return-Path", null);
	$this->append("Received", null);
	$this->append("Message-Id", null);
	$this->append("Resent-Date", null);
	$this->append("Date", null);
	$this->append("Resent-From", null);
	$this->append("From", null);
	$this->append("Reply-To", null);
	$this->append("To", null);
	$this->append("Subject", null);
	$this->append("Cc", null);
	$this->append("In-Reply-To", null);
	$this->append("Resent-Message-Id", null);
	$this->append("Errors-To", null);
	$this->append("Mime-Version", null);
	$this->append("Content-Type", null);
	$this->append("Content-Transfer-Encoding", null);
	$this->append("Content-MD5", null);
	// FIXME
	//$this->append(":", null);
	//$this->append("Content-Length", null);
	//$this->append("Status", null);
    }

// -------------------------------------------------------------------------->

    public function set($name, $value)
    {
	$key = strtolower($name);

	if ( !$this->headers->contains($key) )
	{
	    $this->append($name, $value);
	    return;
	}

	$hdrs = $this->headers->get($key);
	$hdrs->clear();
	$hdrs->add( self::createHeader($name, $value) );
    }

    public function add($name, $value)
    {
	$key = strtolower($name);

	if ( !$this->headers->contains($key) )
	{
	    $this->append($name, $value);
	    return;
	}

	$hdrs = $this->headers->get($key);
	$hdrs->add( self::createHeader($name, $value) );
    }
    
    public function remove($name)
    {
	$this->headers->remove( strtolower($name) );
    }

// -------------------------------------------------------------------------->

    public function getValues($name)
    {
	$hdrs = $this->headers->get( strtolower($name) );
	if ( empty($hdrs) || $hdrs->isEmpty() )
	{
	    return null;
	}

	$values = Array();
	foreach ( $hdrs->elements as $hdr )
	{
	    $values[] = $hdr->getValue();
	}

	return $values;
    }

    public function get($name, $separator=null)
    {
	$values = $this->getValues($name);
	if ( empty($values) )
	{
	    return null;
	}

	if ( sizeof($values) == 1 || empty($separator) )
	{
	    return $values[0];
	}

	$s = '';
	foreach ( $values as $v )
	{
	    if ( !empty($s) )
	    {
		$s = $s . $separator;
	    }

	    $s = $s. $v;
	}

	return $s;
    }

// -------------------------------------------------------------------------->

    private function append($name, $value)
    {
	$hdrs = new ArrayList();

	if ( !empty($value) )
	{
	    $hdrs->add( self::createHeader($name, $value) );
	}

	$this->headers->put(strtolower($name), $hdrs);
    }

    private static function createHeader($name, $value)
    {
	$hdr = new MimeHeader($name, $value);
	return $hdr;
    }

// -------------------------------------------------------------------------->

    public function getLinesIterator()
    {
	$lines = new ArrayList();

	foreach ( $this->headers->keys() as $key )
	{
	    $values = $this->headers->get($key);
	    if ( empty($values) )
	    {
		continue;
	    }
	
	    foreach ( $values->elements as $hdr )
	    {
		$lines->add( $hdr->getLine() );
	    }
	}

	return $lines->getIterator();
    }

    public function getNonMatchingLinesIterator($without)
    {
	$lines	= new ArrayList();

	$except	= new ArrayList();
	if ( !empty($without) )
	{
	    foreach($without as $key)
	    {
		$except->add(strtolower($key));
	    }
	}

	foreach ( $this->headers->keys() as $key )
	{
	    if ( $except->contains($key) )
	    {
		continue;
	    }

	    $values = $this->headers->get($key);
	    if ( empty($values) )
	    {
		continue;
	    }
	
	    foreach ( $values->elements as $hdr )
	    {
		$lines->add( $hdr->getLine() );
	    }
	}

	return $lines->getIterator();
    }
}
?>
