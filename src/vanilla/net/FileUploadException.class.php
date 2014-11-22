<?php

/**
 * 
 */
class FileUploadException extends Exception 
{
    private $phpErrorCode;

//  ------------------------------------------------------->

    public function __construct($message, $phpErrorCode=-1)
    {
	parent::__construct($message);
	$this->phpErrorCode = $phpErrorCode;
    } 

//  ------------------------------------------------------->

    public function getPhpErrorCode()
    {
	return $this->phpErrorCode;
    }
}
?>
