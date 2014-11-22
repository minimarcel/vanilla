<?php

import('vanilla.io.File');
import('vanilla.io.FileReader');
import('vanilla.mail.MimeContentType');
import('vanilla.mail.MimeContentDisposition');

class MimeAttachment
{
    private $fileName;
    private $handler;
    private $contentId;
    private $dispositionType;

// -------------------------------------------------------------------------->

    private function __construct($s, $fileName, /*MimeContentType*/ $type=null)
    {
	if ( empty($type) )
	{
	    $type = self::getDefaultContentType();
	    $type->setParameter("name", $fileName);
	}
	else
	{
	    $name = $type->getParameter("name");
	    if ( empty($name) )
	    {
		$type->setParameter("name", $fileName);
	    }
	}

	$this->fileName = $fileName;
	$this->handler = new DataHandler($s, $type);
    }
    
// -------------------------------------------------------------------------->

    public function getContentType()
    {
	return $this->handler->getContentType();
    }

    public function getContent()
    {
	return $this->handler->getContent();
    }

    public function getFileName()
    {
	return $this->fileName;
    }

    public function getDataHandler()
    {
	return $this->handler;
    }

    public function getName()
    {
	return $this->getContentType()->getParameter("name");
    }

    public function getContentId()
    {
	return $this->contentId;
    }

    public function setContentId($id)
    {
	$this->contentId = trim($id);
    }

    public function setDispositionType($dispositionType)
    {
	$this->dispositionType = $dispositionType;
    }

    public function getContentDisposition()
    {
	$dispositionType = $this->dispositionType;
	if ( empty($dispositionType) )
	{
	    $dispositionType = MimeContentDisposition::ATTACHMENT;
	}

	$disposition = new MimeContentDisposition($dispositionType);
	$disposition->setParameter("filename", $this->fileName);

	return $disposition;
    }

// -------------------------------------------------------------------------->

    public function writeTo(Writer $writer)
    {
	$this->handler->writeTo($writer);	
    }

// -------------------------------------------------------------------------->

    public static function createFromContent($s, $fileName, $type)
    {
	$att = new MimeAttachment($s, self::extractFileName($fileName), $type);
	return $att;
    }

    public static function createFromFile(File $file, $type=null, $fileName=null)
    {
	$reader	= new FileReader($file);
	$content = $reader->read($file->getSize());
	$reader->close();

	$att = new MimeAttachment($content, empty($fileName) ? $file->getName() : $fileName, $type);
	return $att;
    }

    private static function extractFileName($fileName)
    {
	$pos = strripos($fileName, '/');	
	if ( $pos !== false )
	{
	    $fileName = substr($fileName, ++$pos);
	}

	$pos = strripos($fileName, '\\');	
	if ( $pos !== false )
	{
	    $fileName = substr($fileName, ++$pos);
	}

	return $fileName;
    }

    private static function getDefaultContentType()
    {
	$ct = new MimeContentType("application", "octet-stream");
	return $ct;
    }
}
?>
