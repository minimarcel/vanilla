<?php
import('vanilla.util.StringMap');
import('vanilla.net.HTTP');
import('vanilla.net.HttpWebRequest');
import('vanilla.runtime.URLRewriting');

/**
 * 
 */
class WebURL 
{
    /**
     * The file of this url
     */
    protected $file;

    /**
     * The string map of paramters
     */
    protected $parameters;
    
//-------------------------------------------------------------------------->

    public function __construct()
    {
	$this->parameters = new StringMap();
    }

//-------------------------------------------------------------------------->
    
    /**
     * Navigate to the given file
     */
    public function navigate($file, $clearParameters=true)
    {
	$this->file = $this->resolve($file);
	
	if ( $clearParameters === true )
	{
	    $this->parameters->clear();
	}

	return $this;
    }

    public function isCurrentPage()
    {
	// TODO check if is the current page
	return empty($this->file);
    }

    protected function resolve($file)
    {
	if ( !empty($file) && ($file[0] == '/' || startsWith($file, "http://") || startsWith($file, 'https://')) )
	{
	    return $file;
	}

 	$script = empty($this->file) ? HTTP::getRequest()->getScriptName() : $this->file;
	if ( empty($file) )
	{
	    return $script;
	}

	$scriptParts = explode("/", $script);
	$fileParts   = explode("/", $file);

	$end	= sizeof($scriptParts) - 2;
	$start	= 0;

	for ( ; $start < sizeof($fileParts) && $fileParts[$start] == '..' ; $end--, $start++); 

	// error ?
	if ( $end < 0 )
	{
	    return $file;
	}

	$s = "/";
	for ( $i = 0 ; $i <= $end ; $i++ )
	{
	    if ( empty($scriptParts[$i]) )
	    {
		continue;
	    }

	    $s .= $scriptParts[$i] . "/";
	}

	for ( $i = $start ; $i <= sizeof($fileParts) ; $i++ )
	{
	    if ( empty($fileParts[$i]) )
	    {
		continue;
	    }

	    if ( $s[strlen($s)-1] != '/' )
	    {
		$s .= '/';
	    }

	    $s .=  $fileParts[$i];
	}

	return $s;
    }
    
    public function getFile()
    {
	return $this->file;
    }

//-------------------------------------------------------------------------->

    /**
     * Adds a parameter
     */ 
    public function addParameter($name, $value=null)
    {
	if ( empty($name) )
	{
	    // FIXME throw an exception ?
	    return $this;
	}

	$p = $this->parameters->get($name);
	if ( isset($p) )
	{
	    $p[] = $value;
	}
	else
	{
	    $p = Array($value);
	}

	$this->parameters->put($name, $p);

	return $this;
    }

    public function putParameter($name, $value=null)
    {
	if ( empty($name) )
	{
	    // FIXME throw an exception ?
	    return $this;
	}

	$this->removeParameter($name);
	return $this->addParameter($name, $value);
    }

    /**
     * Removes a parameter for the given name
     */
    public function removeParameter($name)
    {
	if ( empty($name) )
	{
	    return null;
	}

	return $this->parameters->remove($name);
    }

    /**
     * Determines whether this URL contains the given parameter name
     */
    public function hasParameter($name)
    {
	return $this->parameters->contains($name);
    }

    /**
     * Gets a parameter value for the given name
     */
    public function getParameter($name)
    {
	$p = $this->getParameterValues($name);
	if ( empty($p) )
	{
	    return null;
	}

	return $p[0];
    }

    /**
     * Gets parameter values list for the given name
     */
    public function getParameterValues($name)
    {
	if ( empty($name) )
	{
	    return null;
	}

	return $this->parameters->get($name);
    }

    public function getParameterNames()
    {
	return $this->parameters->keys();
    }

//-------------------------------------------------------------------------->

    public function toAbsoluteString($rewrite=true, $usePreferedHost=true)
    {
    	if ( startsWith($this->file, "http://") || startsWith($this->file, 'https://') )
    	{
    		return $this->toString(false);
    	}
    	
    	return HTTP::getServerURI($usePreferedHost) . $this->toString($rewrite);
    }

    /**
     * Returns the string representation of this URL.
     */
    public function toString($rewrite=true)
    {
	if ( $rewrite )
	{
	    return $this->rewrite()->toString(false);
	}
	else
	{
	    $s = '';
	    if ( !empty($this->file) )
	    {
		$s = $s . $this->file;
	    }

	    if ( !$this->parameters->isEmpty() )
	    {
		$p = '';
		foreach ( $this->parameters->keys() as $key )
		{
		    $values = $this->parameters->elements[$key];
		    if ( sizeof($values) < 2 )
		    {
			$p = $p . (empty($p) ? '' : '&') . $key . '=' . HTTP::encodeURL($values[0]);
		    }
		    else
		    {
			foreach ( $values as $value )
			{
			    $p = $p . (empty($p) ? '' : '&') . $key . '[]=' . HTTP::encodeURL($value);
			}
		    }
		}

		$s = "$s?$p";
	    }

	    return $s;
	}
    }

    /**
     * Returns the string representation of this URL.
     */
    public function __toString()
    {
	return $this->toString(true);
    }

    public function rewrite()
    {
	return URLRewriting::executeRulesOn($this);
    }

    // TODO method clone

    /**
     * Retourne le contenu de cette URL, en fesant un hit sur le serveur lui même
     */
    public function getContentString()
    {
	$request    = HttpWebRequest::createFromURLString($this->toAbsoluteString());
	$response   = $request->getResponse();
	$output     = $response->getContentString();

	if ( $response->getStatus() / 100 != 2 )
	{
	    throw new IOException("Bad response code : " . $response->getStatus());
	}

	return $output;
    }

//-------------------------------------------------------------------------->

    /**
     * Creates a new URL
     */
    public static function Create($file=null)
    {
	$u = new WebURL();
	if ( !empty($file) )
	{
	    if ( ($i = strpos($file, "?")) !== false )
	    {
		self::parseParameters($u, substr($file, $i + 1));
		$file = substr($file, 0, $i);
	    }

	    $u->navigate($file, false);
	}

	return $u;
    }

    private static function parseParameters($u, $s)
    {
	$params = explode('&', $s);
	foreach ( $params as $param )
	{
	    $nv = explode('=', $param);
	    $key = trim($nv[0]);

	    if ( strlen($key) > 2 && substr($key, -2) == "[]" )
	    {
		$key = substr($key, 0, -2);
	    }

	    $value = isset($nv[1]) ? HTTP::decodeURL($nv[1]) : null;

	    $u->addParameter($key, $value);
	}
    }
}
?>
