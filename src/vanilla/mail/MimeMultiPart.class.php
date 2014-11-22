<?php

import('vanilla.mail.MimeContentType');
import('vanilla.util.ArrayList');
import('vanilla.mail.MimeBodyPart');

class MimeMultiPart
{
    const MIXED		= "mixed";
    const ALTERNATIVE	= "alternative";
    const RELATED	= "related";
    const REPORT	= "report";

// -------------------------------------------------------------------------->

    private $parts;
    private $contentType;
    private $parent;

// -------------------------------------------------------------------------->

    public function __construct($subType)
    {
	if ( empty($subType) )
	{
	    $subType = self::MIXED;
	}

	$boundary = MimeUniqueValue::getUniqueBoundaryValue();

	//creates the content type
	$this->contentType = new MimeContentType("multipart", $subType);
	$this->contentType->setParameter("boundary", $boundary);

	$this->parts = new ArrayList();
    }

// -------------------------------------------------------------------------->

    public function updateHeaders()
    {
	foreach ( $this->parts->elements as $part )
	{
	    $part->updateHeaders();
	}
    }

    public function setParent(MimePart $part)
    {
	$this->parent = $part;
    }

    public function getContentType()
    {
	return $this->contentType;
    }

    public function getParent()
    {
	return $this->parent;
    }

    public function getCount()
    {
	return $this->parts->size();
    }

// -------------------------------------------------------------------------->

    public function getBodyPart($index)
    {
	if ( $index < 0 || $index >= $this->parts->size() )
	{
	    throw new MimeException('Index out of bounds : [0, '.$this->parts->size().'[');
	}

	return $this->parts->get($index);
    }

    public function addBodyPart(MimeBodyPart $part, $index=-1)
    {
	if ( empty($part) )
	{
	    return;
	}

	if ( $index > -1 )
	{
	    $this->parts->insert($index, $part);
	}
	else
	{
	    $this->parts->add($part);
	}

	$part->setParent($this);
    }

// -------------------------------------------------------------------------->

    public function writeTo(Writer $writer)
    {
	$boundary = $this->contentType->getParameter('boundary');
	foreach( $this->parts->elements as $part )
	{
	    $writer->write("--$boundary\r\n");
	    $part->writeTo($writer);
	    $writer->write("\r\n\r\n");
	}

	$writer->write("--$boundary--\r\n");
    }
}
?>
