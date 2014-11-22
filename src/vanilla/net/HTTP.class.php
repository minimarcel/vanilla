<?php

import('vanilla.net.HttpPHPRequest');

/**
 * 
 */
class HTTP
{
    const Javascript_ContentType	= "application/x-javascript";
    const CSS_ContentType		= "text/css";
    const HTML_ContentType		= "text/html";
    const PlainText_ContentType		= "text/plain";
    const XML_ContentType		= "text/xml";
    const RSS_ContentType		= "application/rss+xml";
    const JSON_ContentType		= "application/json";

    const HTTP_Moved_Permanently	= "301";
    const HTTP_Authorization_Required	= "401";
    const HTTP_Forbidden		= "403";
    const HTTP_Not_Found		= "404";
    const HTTP_Server_Error		= "500";

    static $Default_HTTP_Reason_Phrases = Array
    (
	HTTP::HTTP_Moved_Permanently	    => 'Moved permanently',
	HTTP::HTTP_Authorization_Required   => 'Authorization required',
	HTTP::HTTP_Forbidden		    => 'Forbidden',
	HTTP::HTTP_Not_Found		    => 'Page not found',
	HTTP::HTTP_Server_Error		    => 'Server Error'
    );

    private static $request;

//----------------------------------------------->

    public static function setRequest(HttpPHPRequest $request)
    {
	self::$request = $request;
    }

    public static function getRequest()
    {
	if ( empty(self::$request) )
	{
	    self::$request = new HttpPHPRequest();
	}

	return self::$request;
    }

    /**
     * Retourne l'URI du server.
     * si usePreferedHost est à true, utilise la préférence, si elle est définie, HTTP_PREFERED_HOST
     */
    public static function getServerURI($usePreferedHost=false)
    {
	if ( $usePreferedHost && defined('HTTP_PREFERED_HOST') )
	{
	    $protocol   = self::getRequest()->isHTTPS() ? "https" : "http";
	    $port	= defined('HTTP_PREFERED_PORT') ? HTTP_PREFERED_PORT : self::getRequest()->getServerPort();
	    $host	= HTTP_PREFERED_HOST;

	    return self::constructServerURI($host, $protocol, $port);
	}
	else
	{
	    return self::getRequest()->getServerURI();
	}
    }

    public static function constructServerURI($host, $protocol="http", $port=80)
    {
	if ( empty($protocol) )
	{
	    $protocol = "http";
	}

	if ( empty($port) )
	{
	    $port = 80;
	}

	return "$protocol://$host" . ($port == 80 ? "" : ":$port");
    }

//----------------------------------------------->

    /**
     * @param	value	    la valeur du content type
     * @param	filename    le nom du fichier dans le cas ou le content-disposition est un attachment
     */
    public static function setContentType($type, $filename=null, $charset="UTF-8")
    {
	if ( headers_sent() )
	{
	    throw new Exception('Header already sent');
	}

	header('Content-Type: '. $type . '; charset=' . $charset);
	if ( !empty($filename) )
	{
	    header('Content-Disposition: '. "attachment; filename=\"$filename\"");
	}
    }

    public static function redirect($url)
    {
	if ( headers_sent() )
	{
	    echo "<script>document.location.href='$url';</script>\n";
	}
	else
	{
	    $obStatus = ob_get_status();
	    if ( !empty($obStatus) )
	    {
		ob_end_clean(); // clear output buffer
	    }

	    $url = "$url";
	    header('Location: '. $url);
	    self::writeHttpResponseStatusLine(self::HTTP_Moved_Permanently);
	}
    }

    public static function writeHttpResponseStatusLine($responseCode, $reasonPhrase=null)
    {
	if ( headers_sent() )
	{
	    return false;
	}

	if ( empty($reasonPhrase) )
	{
	    $reasonPhrase = self::$Default_HTTP_Reason_Phrases[$responseCode];
	}

	if ( empty($reasonPhrase) )
	{
	    $reasonPhrase = $responseCode;
	}

	header("HTTP/1.1 $responseCode $reasonPhrase");
    }

    public static function writeResponseMessage($responseCode, $message, $title=null)
    {
	if ( empty($title) )
	{
	    $title = self::$Default_HTTP_Reason_Phrases[$responseCode];
	}

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . "\n";
	echo "<html xmlns=\"http://www.w3.org/1999/xhtml\"><head>\n";
	echo "<title>$responseCode $title</title>\n";
	echo "</head><body>\n";
	echo "<h1>$title</h1>\n";
	echo "<p>$message</p>\n";
	echo '</body></html>';
    }

    public static function write403ResponseMessage()
    {
	self::writeResponseMessage(403, 'You\'re not authorized to access the document requested.');
    }

//------------------------------------------------------------------>

    public static function encodeURL($url)
    {
	// TODO use an other method that do not encode /
	$url = urlencode($url);
	$url = str_replace('%2F', '/', $url);
	$url = str_replace('%7C', '|', $url);

	return $url;
    }

    public static function decodeURL($url)
    {
	return urldecode($url);
    }

//----------------------------------------------->

    public static function GET($name)
    {
	$v = self::getVariableFromArray($_GET, $name);
	if ( isset($v) && get_magic_quotes_gpc() && is_string($v) )
	{
	    // FIXME gestion des quotes
	    $v = stripslashes($v);
	}

        return $v;
    }

    public static function POST($name)
    {
	$v = self::getVariableFromArray($_POST, $name);
	if ( isset($v) && get_magic_quotes_gpc() && is_string($v) )
	{
	    // FIXME gestion des quotes
	    $v = stripslashes($v);
	}

        return $v;
    }

    public static function REQUEST($name)
    {
	$v = self::getVariableFromArray($_REQUEST, $name);
	if ( isset($v) && get_magic_quotes_gpc() && is_string($v) )
	{
	    // FIXME gestion des quotes
	    $v = stripslashes($v);
	}

        return $v;
    }

    public static function SESSION($name)
    {
        return self::getVariableFromArray($_SESSION, $name);
    }

    public static function SERVER($name)
    {
        return self::getVariableFromArray($_SERVER, $name);
    }

    public static function COOKIE($name)
    {
        return self::getVariableFromArray($_COOKIE, $name);
    }

    private static function getVariableFromArray(&$array, $name)
    {
        if ( !array_key_exists($name, $array) )
        {
            return null;
        }

        return $array[$name];
    }
}
?>
