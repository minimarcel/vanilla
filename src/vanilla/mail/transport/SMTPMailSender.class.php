<?php

import('vanilla.mail.transport.MailSender');
import('vanilla.mail.transport.SMTPConnection');

/**
 * 
 */
class SMTPMailSender implements MailSender
{
    private $clientName;
    private $serverName;
    private $from;
    private $port;
    private $socket;

//----------------------------------------------->
    
    public function __destruct()
    {
	try
	{
	    $this->finalize();
	}
	catch(Exception $e)
	{}
    }

//----------------------------------------------->

    public function init()
    {
	$this->finalize();
	$this->socket = new SMTPConnection($this->serverName, $this->port);
	$this->socket->identify($this->clientName);
    }

    public function send(Mail $mail)
    {
	MailTransport::applyFilters($mail);

	try
	{
	    $this->socket->newMail(!empty($this->from) ? $this->from : $mail->getFrom());
	    $this->socket->setRecipient($mail->getRecipient()->getAllAddresses());
	    $this->socket->appendMailData($mail->getMimeMessage());
	}
	catch(Exception $e)
	{
	    $this->socket->abortMail();
	    throw $e;
	}
    }

    public function finalize()
    {
	if ( !empty($this->socket) )
	{
	    try
	    {
		$this->socket->close();
	    }
	    catch(Exception $e)
	    {}

	    unset($this->socket);
	}
    }

//----------------------------------------------->

    public function setServerName($serverName)
    {
	$this->serverName = $serverName;
    }

    public function setServerPort($port)
    {
	$this->port = $port;
    }

    public function setClientName($clientName)
    {
	$this->clientName = $clientName;
    }

    public function getServerName()
    {
	return $this->serverName;
    }

    public function getServerPort()
    {
	return $this->port;
    }

    public function getClientName()
    {
	return $this->clientName;
    }

    public function setFrom(InternetAddress $from)
    {
	$this->from = $from;	
    }

    public function getFrom()
    {
	return $this->from;
    }
}
?>
