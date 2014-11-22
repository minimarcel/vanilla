<?php

import('vanilla.mail.transport.BasicMailSender');
import('vanilla.mail.transport.MailSender');
import('vanilla.mail.Mail');

/**
 * 
 */
class MailTransport
{
    private static $senders; 
    private static $defaultSender;
    // TODO receivers
    // FIXME sessions ?

//----------------------------------------------->

    public static function getDefaultSender()
    {
	if ( empty(self::$defaultSender) )
	{
	    self::$defaultSender = new BasicMailSender();
	}

	return self::$defaultSender;
    }

    public static function getSenderForName($name)
    {
	if ( empty(self::$senders) )
	{
	    return null;
	}

	return self::$senders->get($name);
    }

    public static function send(Mail $mail, $senderName=null)
    {
	$sender = empty($senderName) ? self::getDefaultSender() : self::getSenderForName($senderName);
	$sender->init();
	$sender->send($mail);
	$sender->finalize();
    }

    /**
     *
     */
    public static function applyFilters($mail)
    {
	// ajoute une liste de BCC a tous les mais envoyés
	if ( defined('MAILSENDER_BCC') )
	{
	    self::addBccRecipients($mail, MAILSENDER_BCC);
	}

	// on vérifie les adresse authorisées
	if ( defined('MAILSENDER_AUTHORIZED_ADDRESSES') )
	{
	    self::checkAuthorizedAddresses($mail, MAILSENDER_AUTHORIZED_ADDRESSES);
	}
    }

    private static function checkAuthorizedAddresses(Mail $mail, $list)
    {
	// on parse la list
	$authorized = new ArrayList();
	foreach ( explode(',', $list) as $add )
	{
	    $add = trim($add);
	    if ( !empty($add) )
	    {
		$authorized->add($add);
	    }
	}

	if ( $authorized->isEmpty() )
	{
	    return;
	}

	$recipient = $mail->getRecipient();

	// on enlève 
	foreach ( Array(MimeRecipient::TO, MimeRecipient::CC, MimeRecipient::BCC) as $recipientType )
	{
	    $addresses = $recipient->getAddresses($recipientType);
	    if ( empty($addresses) )
	    {
		continue;
	    }

	    // on les filtre les adresses
	    foreach ( $addresses as $add )
	    {
		if ( !$authorized->contains($add->getAddress()) )
		{
		    $add->setAddress($authorized->get(0));
		}
	    }
	}

	// si il n'y a aucune adresse on ajoute la première des authorisées
	$addresses = $recipient->getAllAddresses();
	if ( empty($addresses) )
	{
	    $recipient->add(InternetAddress::create($authorized->get(0)), MimeRecipient::TO);
	}
    }

    private static function addBccRecipients(Mail $mail, $list)
    {
	// on parcours la list
	foreach ( explode(',', $list) as $add )
	{
	    $add = trim($add);
	    if ( !empty($add) )
	    {
		$mail->addBcc(InternetAddress::create($add));
	    }
	}
    }

//----------------------------------------------->

    public static function addSender($name, $senderClass, $isDefault=true)
    {
	$sender = self::loadSender($name, $senderClass);
	if ( $isDefault )
	{
	    self::$defaultSender = $sender;
	}

	return $sender;
    }

    private static function loadSender($name, $senderClass)
    {
	if ( empty(self::$senders) )
	{
	    self::$senders = new StringMap();
	}
	
	$classname = import($senderClass);
	$sender = new $classname();
	self::$senders->put($name, $sender);
	
	return $sender;
    }
}
?>
