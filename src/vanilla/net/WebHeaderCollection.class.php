<?php

import('vanilla.util.ArrayList');
import('vanilla.util.StringMap');
import('vanilla.net.WebHeader');

class WebHeaderCollection
{
    private $headers;

// -------------------------------------------------------------------------->

    public function __construct()
    {
	$this->headers = new StringMap();

	// on définit des headers par défaut pour garder la casse
	$this->append("Accept", null);
	$this->append("Accept-Charset", null);
	$this->append("Accept-Encoding", null);
	$this->append("Accept-Language", null);
	$this->append("Accept-Ranges", null);
	$this->append("Age", null);
	$this->append("Allow", null);
	$this->append("Authorization", null);
	$this->append("Cache-Control", null);
	$this->append("Connection", null);
	$this->append("Cookie", null);
	$this->append("Content-Encoding", null);
	$this->append("Content-Language", null);
	$this->append("Content-Length", null);
	$this->append("Content-Location", null);
	$this->append("Content-Disposition", null);
	$this->append("Content-MD5", null);
	$this->append("Content-Range", null);
	$this->append("Content-Type", null);
	$this->append("Date", null);
	$this->append("ETag", null);
	$this->append("Expect", null);
	$this->append("Expires", null);
	$this->append("From", null);
	$this->append("Host", null);
	$this->append("If-Match", null);
	$this->append("If-Modified-Since", null);
	$this->append("If-None-Match", null);
	$this->append("If-Range", null);
	$this->append("If-Unmodified-Since", null);
	$this->append("Last-Modified", null);
	$this->append("Location", null);
	$this->append("Max-Forwards", null);
	$this->append("Pragma", null);
	$this->append("Proxy-Authorization", null);
	$this->append("Range", null);
	$this->append("Retry-After", null);
	$this->append("Referer", null);
	$this->append("Server", null);
	$this->append("Set-Cookie", null);
	$this->append("TE", null);
	$this->append("Trailer", null);
	$this->append("Transfer-Encoding", null);
	$this->append("Upgrade", null);
	$this->append("User-Agent", null);
	$this->append("Vary", null);
	$this->append("Via", null);
	$this->append("Warn", null);
	$this->append("Warning", null);
	$this->append("WWW-Authenticate", null);
    }

// -------------------------------------------------------------------------->

    public function set($name, $value)
    {
	$key = strtolower($name);

	if ( !$this->headers->contains($key) )
	{
	    $this->append($name, $value);
	    return $this;
	}

	$hdrs = $this->headers->get($key);
	$hdrs->clear();
	$hdrs->add( self::createHeader($name, $value) );

	return $this;
    }

    public function add($name, $value)
    {
	$key = strtolower($name);

	if ( !$this->headers->contains($key) )
	{
	    $this->append($name, $value);
	    return $this;
	}

	$hdrs = $this->headers->get($key);
	$hdrs->add( self::createHeader($name, $value) );

	return $this;
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
	$hdr = new WebHeader($name, $value);
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
}
?>
