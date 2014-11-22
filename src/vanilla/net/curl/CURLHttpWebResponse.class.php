<?php

import('vanilla.net.HttpWebResponse');
import('vanilla.io.File');
import('vanilla.io.FileReader');
import('vanilla.io.FileWriter');

class CURLHttpWebResponse extends HttpWebResponse
{
    private $info;
    private $tmpFile;
    private $reader = null;

//  ------------------------------------------------------------->

    public function __construct(CURLHttpWebRequest $request, $resource)
    {
	parent::__construct($request);

	/*
	   On écrit le résultat dans un fichier temporaire
	    FIXME : tmpfile ne marche pas avec curl dans la version 5.2.9 et 5.2.10, celà semble être 
	    corrigé dans les version utlérieures
	*/

	$this->tmpFile = new File(tempnam(realpath(sys_get_temp_dir()), "curl"));
	$writer = new FileWriter($this->tmpFile);
	curl_setopt($resource, CURLOPT_FILE, $writer->getStream());
	curl_setopt($resource, CURLOPT_HEADERFUNCTION, array($this,'readHeader'));

	curl_exec($resource);
	$this->info = curl_getinfo($resource);

	$writer->close();
    }

    public function __destruct()
    {
	if ( !empty($this->reader) )
	{
	    $this->reader->close();
	}

	if ( !empty($this->tmpFile) )
	{
	    $this->tmpFile->delete();
	}
    }

//  ------------------------------------------------------------->

    /**
     * Retourne le reader positionné au début du content
     */
    protected function getTextReader()
    {
	if ( empty($this->reader) )
	{
	    // FiXME comment connnait on le charset en entrée ?
	    $this->reader = new FileReader($this->tmpFile);
	}

	return $this->reader;
    }

    public function getURL()
    {
	return $this->info['url'];
    }

    // pour l'instant on surcharge cette partie
    public function getStatus()
    {
	return $this->info['http_code'];
    }

    public function readHeader($resource, $header)
    {
	$l = strlen($header);
	$v = explode(':', trim($header), 2);

	if ( sizeof($v) == 2 )
	{
	    list($name, $val) = $v;
	    $this->headers->add($name, $val);
	}

	return $l;
    }
}
?>
