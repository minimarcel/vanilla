<?php
import('vanilla.net.WebURL');

/**
 * 
 */
class URLRW extends WebURL
{
    private $fileWithoutContext;

//  ---------------------------------------->

    public function __construct(WebURL $url)
    {
	parent::__construct();

	// TODO clone map and lists
	$this->file = $url->file;
	foreach( $url->parameters->elements as $key => $value )
	{
	    $this->parameters->put($key, $value);
	}

	$this->computeFileWithoutContext();
    }

//  ---------------------------------------->

    public function navigate($file, $clearParameters=true)
    {
	parent::navigate($file, $clearParameters);
	$this->computeFileWithoutContext();
    }

    private function computeFileWithoutContext()
    {
	// FIXME doit on tester si le file commence bien par ça ?
	$file = substr($this->file, strlen(WWW_URL));
	$this->fileWithoutContext = "/$file";
    }

    public function getFileWithoutContext()
    {
	return $this->fileWithoutContext;
    }

    public function toString($rewrite=true)
    {
	return parent::toString(false);
    }

    public function __toString()
    {
	return $this->toString(false);
    }
}
?>
