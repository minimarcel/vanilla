<?php

import('vanilla.mail.transport.MailSender');

/**
 * 
 */
class BasicMailSender implements MailSender
{
    // accept values : CR, LF, CRLF
    private $lineFeed = 'CRLF'; 

//  ------------------------------------------------>

    public function init()
    {}

    public function send(Mail $mail)
    {
	MailTransport::applyFilters($mail);

	$message    = $mail->getMimeMessage();	
	$to	    = InternetAddress::toAddressLine( $mail->getRecipient()->getAllAddresses() );
	$subject    = $message->getHeader('Subject');

	// FIXME gestion des Recipients, peut-être que pour send mail, les Cc, To et Bcc doivent être passés ?
	$writer    = new StringWriter();
	$message->writeHeaderTo($writer, Array('To', 'Bcc', 'Subject'));
	$headers = $writer->getBuffer();

	$writer->clear();
	$message->writeContentTo($writer);
	$content = $writer->getBuffer();

	mail($to, $subject, $this->handleLineFeed($content), $this->handleLineFeed($headers));

	$writer->close();
    }

    public function finalize()
    {}

    private function handleLineFeed($v)
    {
	switch($this->lineFeed)
	{
	    case 'CR' : 
	    {
		$v = str_replace("\r\n", "\r", $v);
	    }
	    break;

	    case 'LF' : 
	    {
		$v = str_replace("\r\n", "\n", $v);
	    }
	    break;

	    case 'CRLF' : 
	    {
		// noting to do    
	    }
	    break;
	}

	return $v;
    }

    // accept values : CR, LF, CRLF
    public function setLineFeed($lineFeed)
    {
	if ( $lineFeed == 'CR' || $lineFeed == 'LF' || $lineFeed == 'CRLF' )
	{
	    $this->lineFeed = $lineFeed;
	}
    }
}
?>
