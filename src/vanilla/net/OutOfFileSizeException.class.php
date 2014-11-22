<?php

import('vanilla.net.FileUploadException');

/**
 * 
 */
class OutOfFileSizeException extends FileUploadException 
{
    private $size;

//  ------------------------------------------------------->

    public function __construct($phpErrorCode=-1, /*string*/ $size=null)
    {
	parent::__construct(self::computeMessage($phpErrorCode, $size), $phpErrorCode);
	$this->size = self::extractMaxSize($phpErrorCode, $size);
    } 

//  ------------------------------------------------------->

    public function getMaxSize()
    {
	return $this->size;
    }

//  ------------------------------------------------------->

    private static function computeMessage($phpErrorCode, $size)
    {
	$size = self::extractMaxSize($phpErrorCode, $size);
	return "The uploaded file size exceeds the max defined size : $size";
    }

    private static function extractMaxSize($phpErrorCode, $size)
    {
	if ( !empty($size) )
	{
	    return $size . "b"; 
	}

	if ( $phpErrorCode == UPLOAD_ERR_INI_SIZE )
	{
	    return ini_get("upload_max_filesize");
	}
	else if ( $phpErrorCode == UPLOAD_ERR_FORM_SIZE )
	{
	    return $_POST["MAX_FILE_SIZE"] . "b";
	}

	return null;
    }
}
?>
