<?php

import('vanilla.net.Socket');
import('vanilla.net.HTTP');
import('vanilla.mail.InternetAddress');
import('vanilla.mail.MimeMessage');
import('vanilla.mail.transport.SMTPWriter');

/*
----COMPLETE----

211     System status, or system help reply 
214     Help message
        [Information on how to use the receiver or the meaning of a particular non-standard command;
         his reply is useful only to the human user] 
220     <domain> Service ready 
221     <domain> Service closing transmission channel 
250     Requested mail action okay, completed 
251     User not local; will forward to <forward-path> 

-----CONTINUE----
354     Start mail input; end with <CRLF>.<CRLF> 

-----TRANSIANT----
421     <domain> Service not available, closing transmission channel
        [This may be a reply to any command if the service knows it must shut down] 
450     Requested mail action not taken: mailbox unavailable
        [E.g., mailbox busy] 
451     Requested action aborted: local error in processing 
452     Requested action not taken: insufficient system storage 

-----ERROR----
500     Syntax error, command unrecognized
        [This may include errors such as command line too long] 
501     Syntax error in parameters or arguments
502     Command not implemented
503     Bad sequence of commands
504     Command parameter not implemented
550     Requested action not taken: mailbox unavailable [E.g., mailbox not found, no access] 
551     User not local; please try <forward-path> 
552     Requested mail action aborted: exceeded storage allocation
553     Requested action not taken: mailbox name not allowed
        [E.g., mailbox syntax incorrect] 
554     Transaction failed 
*/
class SMTPConnection extends Socket
{
    const REPLY_COMPLETE	= 2;
    const REPLY_CONTINUE	= 3;
    const REPLY_TRANSIENT	= 4;
    const REPLY_ERROR		= 5;

// -------------------------------------------------------------------------->

    private $identified		= false;
    private $opened	    	= false;

// -------------------------------------------------------------------------->

    public function __construct($target, $port=25)
    {
        parent::__construct($target, empty($port) ? 25 : $port, 5);
    }

    public function identify($clientName=null)
    {
	if ( !$this->opened )
	{
	    if ( $this->getReply() == self::REPLY_ERROR )
	    {
		throw new IOException("Aucune réponse depuis : " . $this->getTarget());	
	    }

	    $this->getWriter()->setLineSeparator("\r\n");
	    $this->opened = true;
	}

	if ( $this->identified )
	{
	    throw new IOException("Connection déjà identifié");
	}

	if ( empty($clientName) )
	{
	    $clientName = HTTP::getRequest()->getServerName();
	}

	if ( empty($clientName) )
	{
	    $clientName = "localhost";
	}

	if ( $this->sendCommand("HELO $clientName") == self::REPLY_COMPLETE )
	{
	    $this->identified = true;
	    return true;
	}

	return false;
    }

    private function checkValidity()
    {
	if ( !$this->identified )
	{
	    throw new IOException("La connection être identifié d'abord");
	}
    }

    public function close()
    {
	if ( $this->opened )
	{
	    $this->opened	= false;
	    $this->identified	= false;
	    $this->sendCommand("QUIT");
	}
    }

// -------------------------------------------------------------------------->

    public function newMail(InternetAddress $address)
    {
	$this->checkValidity();
	$s = $address->getLine(false);
	return ($this->sendCommand("MAIL FROM: $s") === self::REPLY_COMPLETE);
    }

    public function abortMail()
    {
	$this->checkValidity();
	return ($this->sendCommand("RSET") === self::REPLY_COMPLETE);
    }
    
    /**
     * Définit tous les destinataires du mail, y compris les Bcc et Cc
     *
     * @param	$addresses  un tableau de InternetAddress
     */
    public function setRecipient($addresses)
    {
	$this->checkValidity();
	foreach( $addresses as $address )
	{
	    $s = $address->getLine(false);
	    if( $this->sendCommand("RCPT TO: $s") !== self::REPLY_COMPLETE )
	    {
		return false;
	    }
	}

	return true;
    }

    public function appendMailData(MimeMessage $message)
    {
	$this->checkValidity();
	if ( $this->sendCommand("DATA") !== self::REPLY_CONTINUE )
	{
	    return false;
	}

	$message->setSentDate(Date::current());

	$writer	    = $this->getWriter();	
	$smtpWriter = new SMTPWriter($writer);
	$message->writeTo($smtpWriter, Array('Bcc', 'Content-Length'));

	$writer->writeln();
	return ($this->sendCommand(".") === self::REPLY_COMPLETE);
    }

// -------------------------------------------------------------------------->

    private function getReply()
    {
	$result = $this->getReader()->readln();
	if ( empty($result) )
	{
	    return self::REPLY_ERROR;
	}

	debug("< $result");
	$code = intval($result[0]);
	if ( $code > 3 )
	{
	    throw new IOException("Bad smtp reply : $result");
	}

	return $code;
    }

    private function sendCommand($cmd)
    {
	debug("> $cmd");
	$this->getWriter()->writeln($cmd);
	return $this->getReply();
    }
}
