<?php

import('vanilla.util.Date');
import('vanilla.mail.MimePart');
import('vanilla.mail.MimeUniqueValue');
import('vanilla.mail.InternetAddress');
import('vanilla.mail.MimeRecipient');

class MimeMessage extends MimePart
{
    const MIME_VERSION	    = '1.0';
    const MAILER	    = 'Vanilla Mailer 1.0';

// -------------------------------------------------------------------------->

    private $saved = false;

// -------------------------------------------------------------------------->

    public function __construct()
    {
	parent::__construct();
	$this->saved = false;
    }

// -------------------------------------------------------------------------->

    public function writeHeaderTo(Writer $writer, $withoutHeaders=null)
    {
	if ( !$this->saved )
	{
	    $this->save();
	}

	return parent::writeHeaderTo($writer, $withoutHeaders);
    }

// -------------------------------------------------------------------------->

    public function setDataHandler(DataHandler $dh)
    {
	$this->saved = false;
	parent::setDataHandler($dh);
    }

    public function updateHeaders()
    {
	$from = $this->getFrom();

	$this->setHeader("Mime-Version",       self::MIME_VERSION);
	$this->setHeader("X-Mailer",           self::MAILER);
	$this->setHeader("Message-ID",         "<" . MimeUniqueValue::getUniqueMessageId($from) . ">");

	parent::updateHeaders();
    }

    public function save()
    {
	$this->updateHeaders();

	//set saved at true
	$this->saved = true;
    }

// -------------------------------------------------------------------------->

    public function setFrom(InternetAddress $address)
    {
	if ( empty($address) )
	{
	    $this->removeHeader("From");
	}
	else
	{
	    $this->setHeader("From", $address->getLine());
	}
    }

    public function setRecipient($addresses, $recipientType)
    {
	MimeRecipient::checkType($recipientType);
	$this->setAddressHeader($recipientType, $addresses);
    }

    public function setRecipientObject(MimeRecipient $recipient)
    {
	$this->setRecipient($recipient->getAddresses(MimeRecipient::TO), MimeRecipient::TO);
	$this->setRecipient($recipient->getAddresses(MimeRecipient::CC), MimeRecipient::CC);
    }

    private function setAddressHeader($name, $addresses)
    {
	$addressList = InternetAddress::toAddressLine($addresses);
	if( empty($addressList) )
	{
	    $this->removeHeader($name);
	}
	else
	{
	    $this->setHeader($name, $addressList);
	}
    }

// -------------------------------------------------------------------------->

    public function setSubject($subject, $charset=null)
    {
	if ( empty($subject) )
	{
	    $this->removeHeader('Subject');
	}
	else
	{
	    // suppression des retours à la ligne
	    // toujours pour Outlook qui n'accèpte pas de retour chariot
	    $subject = str_replace("\r\n", " ", $subject);
	    $subject = str_replace("\r", " ", $subject);
	    $subject = str_replace("\n", " ", $subject);

	    // FIXME utilisation de l'encodeWord, visiblement outlook n'accèpte pas l'encodeText pour le sujet ..
	    //$this->setHeader("Subject", MimeUtil::encodeText($subject, $charset));
	    $this->setHeader("Subject", MimeUtil::encodeWord($subject, $charset));
	}
    }

    public function setSentDate(Date $date)
    {
	if ( empty($date) )
	{
	    $this->removeHeader('Date');
	}
	else
	{
	    $s = $date->rfc822() . " " . $date->format("(%Z)");
	    $this->setHeader("Date", $s);
	}
    }

// -------------------------------------------------------------------------->

    public function getFrom()
    {
	// TODO
    }
}
?>
