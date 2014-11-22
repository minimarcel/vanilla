<?php

import('vanilla.mail.MimePartHeaders');
import('vanilla.mail.MimeMultiPart');
import('vanilla.mail.MimeAttachment');
import('vanilla.mail.DataHandler');
import('vanilla.mail.MimeContentDisposition');
import('vanilla.mail.MimeUtil');

abstract class MimePart
{
    private $dataHandler;
    private $headers;

// -------------------------------------------------------------------------->

    protected function __construct()
    {
	$this->headers = new MimePartHeaders();
    }

// -------------------------------------------------------------------------->
// Ecriture 

    public function writeTo(Writer $writer, $withoutHeaders=null)
    {
	/*
	   Ecriture des headers
	*/

	$this->writeHeaderTo($writer, $withoutHeaders);
	$writer->write("\r\n");

	/*
	   Ecriture du contenu
	*/

	$this->writeContentTo($writer);
    }

    public function writeHeaderTo(Writer $writer, $withoutHeaders=null)
    {
	for ( $it = $this->headers->getNonMatchingLinesIterator($withoutHeaders) ; $it->hasNext() ; )
	{
	    $writer->write($it->next() . "\r\n");
	}
    }

    public function writeContentTo(Writer $writer)
    {
	$writer = MimeUtil::getWriterToEncode($writer, $this->getEncoding());
	if ( !empty($this->dataHandler) )
	{
	    $this->dataHandler->writeTo($writer);
	}
    }

// -------------------------------------------------------------------------->
// Définition du content 

    public function setMultiPartContent(MimeMultiPart $multi)
    {
	$dh = new DataHandler($multi, $multi->getContentType());
	$this->setContentForType($multi, $multi->getContentType());
	$multi->setParent($this);
    }

    public function setAttachmentContent(MimeAttachment $att, $setDisposition=true)
    {
	$this->setDataHandler( $att->getDataHandler() );
	if ( $setDisposition )
	{
	    $this->setContentDisposition( $att->getContentDisposition() );
	}

	$contentId = $att->getContentId();
	if ( !empty($contentId) )
	{
	    $this->setHeader('Content-ID', "<$contentId>");
	}
    }

    public function setPlainTextContent($text, $charset=null)
    {
	$charset = self::getCharsetFor($text, $charset);
	$type = new MimeContentType("text", "plain");
	$type->setParameter("charset", $charset);

	$this->setContentForType($text, $type);
    }

    public function setHtmlTextContent($html, $charset=null)
    {
	$charset = self::getCharsetFor($html, $charset);
	$type = new MimeContentType("text", "html");
	$type->setParameter("charset", $charset);

	$this->setContentForType($html, $type);
    }

    private static function getCharsetFor($s, $charset=null)
    {
	if ( !empty($charset) )
	{
	    return $charset;
	}

	if ( MimeUtil::checkASCII($s) != MimeUtil::ASCII )
	{
	    return MimeUtil::$DefaultCharset;
	}

	return "us-ascii";
    }

    public function setContentForType($content, MimeContentType $type)
    {
	$dh = new DataHandler($content, $type);
	$this->setDataHandler($dh);
    }

    protected function setDataHandler(DataHandler $dh)
    {
	$this->dataHandler = $dh;
	$this->invalidHeaders();
    }

    public function getContent()
    {
	if ( empty($this->dataHandler) )
	{
	    return null;
	}
	
	return $this->getContent();
    }

    public function getDataHandler()
    {
	return $this->dataHandler;
    }

// -------------------------------------------------------------------------->
// Définition des headers

    public function setHeader($name, $value)
    {
	$this->headers->set($name, $value);
    }

    public function removeHeader($name)
    {
	$this->headers->remove($name);
    }

    public function setEncoding($encoding)
    {
	$this->setHeader('Content-Transfer-Encoding', $encoding);
    }

    public function getEncoding()
    {
	return $this->getHeader('Content-Transfer-Encoding');
    }

    public function setContentDisposition(MimeContentDisposition $disposition)
    {
	$this->setHeader('Content-Disposition', $disposition->getLine());
    }

    public function getContentDisposition()
    {
	// TODO parse content disposition
	return null;
    }

    /**
     * Retourne un tableau de valeurs pour le header donné.
     */
    public function getHeaderValues($name)
    {
	return $this->headers->getValues($name);
    }

    /**
     * Retourne toutes les valeurs d'un header séparée par le séparateur donné.
     */
    public function getHeader($name, $separator=null)
    {
	return $this->headers->get($name, $separator);
    }

    protected function invalidHeaders()
    {
	$this->removeHeader('Content-Type');
	$this->removeHeader('Content-Transfer-Encoding');
    }

    protected function updateHeaders()
    {
	if ( empty($this->dataHandler) )
	{
	    return;
	}

	$type = $this->dataHandler->getContentType();
	$noContentType = false;

	if ( $type->match("multipart", "*") )
	{
	    $noContentType = true;
	    $this->dataHandler->getContent()->updateHeaders();
	}
	else if ( $type->match("message", "rfc822") )
	{
	    $noContentType = true;
	}

	$typeHeader = $this->getHeader("Content-Type");
	if( empty($typeHeader) )
	{
	    $dispos = $this->getContentDisposition();
	    if ( !empty($dispos) )
	    {
		$fileName   = $dispos->getParameter("filename");
		$name	    = $type->getParametert("name");

		if ( !empty($fileName) && empty($namme) )
		{
		    $type->setParameter("name", $fileName);
		}
	    }

	    $this->setHeader("Content-Type", $type->getLine());
	}

	$encoding = $this->getEncoding();
	if( !$noContentType && empty($encoding) )
	{
	    $this->setEncoding( MimeUtil::getEncoding($this->dataHandler) );
	}
    }
    
// -------------------------------------------------------------------------->

    public function __toString()
    {
	$writer = new StringWriter();
	$this->writeTo($writer);
	return $writer->getBuffer();
    }
}
?>
