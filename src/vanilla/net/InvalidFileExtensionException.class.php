<?php

import('vanilla.net.FileUploadException');

/**
 * 
 */
class InvalidFileExtensionException extends FileUploadException 
{
    private $extensions;

//  ------------------------------------------------------->

    public function __construct($phpErrorCode=-1, /*Array<String>*/ $extensions=null)
    {
	parent::__construct(self::computeMessage($phpErrorCode, $extensions), $phpErrorCode);
	$this->extensions = $extensions;
    } 

//  ------------------------------------------------------->

    public function getExtensions()
    {
	return $this->extensions;
    }

//  ------------------------------------------------------->

    private static function computeMessage($phpErrorCode, $extensions)
    {
	return "The uploaded file has'nt a valid extension" . (empty($extensions) ? "" : "; " . implode(", ", $extensions));
    }
}
?>
