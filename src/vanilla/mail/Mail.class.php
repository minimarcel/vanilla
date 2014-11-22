<?php

import('vanilla.mail.MimeMessage');
import('vanilla.mail.MimeAttachment');

class Mail
{
    private $from;
    private $recipient;
    private $subject;
    private $plainText;
    private $htmlText;
    private $attachments;
    private $relatedAttachments;
    private $charset;

    private $message;

// -------------------------------------------------------------------------->
    
    public function __construct($charset=null)
    {
	$this->charset = $charset;
	$this->recipient = new MimeRecipient();
    }

// -------------------------------------------------------------------------->
    
    public function setFrom(InternetAddress $addr)
    {
	$this->from = $addr;
	if ( !empty($this->message) )
	{
	    $this->message->setFrom($addr);
	}
    }

    public function getFrom()
    {
	return $this->from;
    }
    
// -------------------------------------------------------------------------->

    public function setPlainText($text)
    {
	$this->plainText = $text;
	$this->destroyMessage();
    }

    public function setHTMLText($text)
    {
	$this->htmlText = $text;
	$this->destroyMessage();
    }

// -------------------------------------------------------------------------->

    public function addTo(InternetAddress $to)
    {
	$this->addRecipient($to, MimeRecipient::TO);
    }

    public function addCc(InternetAddress $cc)
    {
	$this->addRecipient($cc, MimeRecipient::CC);
    }

    public function addBcc(InternetAddress $bcc)
    {
	$this->addRecipient($bcc, MimeRecipient::BCC);
    }

    public function clearTo()
    {
	$this->clearRecipient(MimeRecipient::TO);
    }

    public function clearCc()
    {
	$this->clearRecipient(MimeRecipient::CC);
    }

    public function clearBcc()
    {
	$this->clearRecipient(MimeRecipient::BCC);
    }

    public function addRecipient(InternetAddress $addr, $recipientType)
    {
	$this->recipient->add($addr, $recipientType);
	if ( !empty($this->message) )
	{
	    $this->message->setRecipientObject($this->recipient);
	}
    }

    public function clearRecipient($recipientType)
    {
	$this->recipient->clear($recipientType);
	if ( !empty($this->message) )
	{
	    $this->message->setRecipientObject($this->recipient);
	}
    }

    public function getRecipient()
    {
	return $this->recipient;
    }

// -------------------------------------------------------------------------->

    public function setSubject($subject)
    {
	$this->subject = $subject;
	if ( !empty($this->message) )
	{
	    $this->message->setSubject($this->subject);
	}
    }

// -------------------------------------------------------------------------->

    public function addAttachment(MimeAttachment $att)
    {
	if ( empty($att) )
	{
	    return;
	}

	if ( empty($this->attachments) )
	{
	    $this->attachments = new ArrayList();
	}

	$this->attachments->add($att);
	$this->destroyMessage();
    }

    public function clearAttachment()
    {
	$this->attachments = null;
	$this->destroyMessage();
    }

    public function addRelatedAttachment(MimeAttachment $att)
    {
	if ( empty($att) )
	{
	    return;
	}

	if ( empty($this->relatedAttachments) )
	{
	    $this->relatedAttachments = new ArrayList();
	}

	$id = $att->getContentId();
	if ( empty($id) )
	{
	    $att->setContentId( MimeUniqueValue::getUniqueMessageId() );
	}

	$this->relatedAttachments->add($att);
	$this->destroyMessage();
    }

    public function clearRelatedAttachment()
    {
	$this->relatedAttachments = null;
	$this->destroyMessage();
    }

// -------------------------------------------------------------------------->

    private function destroyMessage()
    {
	$this->message = null;
    }

    public function getMimeMessage()
    {
	if ( !empty($this->message) )
	{
	    return $this->message;
	}

	$msg = new MimeMessage();
	$msg->setFrom($this->from);
	$msg->setSubject($this->subject);
	$msg->setRecipientObject($this->recipient);

	/*
	   Contenu
	*/

	$part = $msg;
	$part = $this->createAttachments($part);
	$part = $this->createRelatedAttachments($part);

	$this->setTextContent($part, $this->plainText, $this->htmlText);

	$this->message = $msg;
	return $msg;
    }

    private function createAttachments(MimePart $part)
    {
	$multi = new MimeMultiPart(MimeMultiPart::MIXED);

	$this->createAttachmentParts($multi, $this->attachments, true);

	if ( $multi->getCount() > 0 )
	{
	    $part->setMultiPartContent($multi);
	    $part = new MimeBodyPart();
	    $multi->addBodyPart($part, 0);
	}

	return $part;
    }

    private function createRelatedAttachments(MimePart $part)
    {
	$multi = new MimeMultiPart(MimeMultiPart::RELATED);
	$multi->getContentType()->setParameter("type", "multipart/" . MimeMultiPart::ALTERNATIVE);

	$this->createAttachmentParts($multi, $this->relatedAttachments, false);

	if ( $multi->getCount() > 0 )
	{
	    $part->setMultiPartContent($multi);
	    $part = new MimeBodyPart();
	    $multi->addBodyPart($part, 0);
	}

	return $part;
    }

    private function createAttachmentParts(MimeMultiPart $multi, $attachments, $setDisposition)
    {
	if ( empty($attachments) )
	{
	    return;
	}

	foreach ( $attachments->elements as $att )
	{
	    $body = new MimeBodyPart();
	    $body->setAttachmentContent($att, $setDisposition);
	    $multi->addBodyPart($body);
	}
    }

    private function setTextContent(MimePart $part, $plain, $html)
    {
	if ( empty($plain) && empty($html) )
	{
	    $part->setPlainTextContent('', $this->charset);
	}
	else if ( !empty($plain) && !empty($html) )
	{
	    $multi = new MimeMultiPart(MimeMultiPart::ALTERNATIVE);

	    $body = new MimeBodyPart();
	    $this->setTextContent($body, $plain, null);
	    $multi->addBodyPart($body);

	    $body = new MimeBodyPart();
	    $this->setTextContent($body, null, $html);
	    $multi->addBodyPart($body);

	    $part->setMultiPartContent($multi);
	}
	else if ( !empty($plain) )
	{
	    $part->setPlainTextContent($plain, $this->charset);
	}
	else
	{
	    $part->setHtmlTextContent($html, $this->charset);
	}
    }
}
?>
