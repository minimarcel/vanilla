<?php

import('vanilla.runtime.URLRewritingRule');
import('wisy.WisyURL');

/**
 * 
 */
class RemovePhpExtensionRule implements URLRewritingRule
{
    /**
     *
     */
    public function init(URLRewritingConfig $config)
    {}

    /**
     *
     */
    public function execute(URLRW $url)
    {
	/*
	   Si le fichier est un fichier php,
	   on enlève son extension
	*/

	$file	= $url->getFile();	
	$newOne = $this->removeSuffix($file, ".php");
	if ( $newOne !== $file )
	{
	    $file = self::removeSuffix($newOne, "index");
	    $url->navigate($file, false);
	}

	return $url;
    }

    private function removeSuffix($file, $suffix)
    {
	if ( strlen($file) > strlen($suffix) && substr($file, -strlen($suffix)) == $suffix )
	{
	    $file = substr($file, 0, -strlen($suffix));
	}

	return $file;
    }
}
?>
