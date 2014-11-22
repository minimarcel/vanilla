<?php

import('vanilla.mail.MimeContentType');
import('vanilla.mail.MimeException');
import('vanilla.io.Writer');

class DataHandler
{
    private $content;
    private $contentType;

// -------------------------------------------------------------------------->

    public function __construct($content, MimeContentType $contentType)
    {
	if ( !isset($content) )
	{
	    throw new MimeException("No content");
	}

	if ( empty($contentType) )
	{
	    throw new MimeException("No content type");
	}

	$this->content	    = $content;
	$this->contentType  = $contentType;
    }

// -------------------------------------------------------------------------->

    public function getContent()
    {
	return $this->content;
    }

    public function getContentType()
    {
	return $this->contentType;
    }

// -------------------------------------------------------------------------->

    public function writeTo(Writer $writer)
    {
	if ( $this->contentType->match("multipart", "*") )
	{
	    $this->content->writeTo($writer);
	}
	else if ( $this->contentType->match("message", "rfc822") )
	{
	    $this->content->writeTo($writer);
	}
	else
	{
	    $writer->write($this->content);
	}
    }
}
?>
