<?php

import('vanilla.util.StringMap');
import('vanilla.util.ArrayList');
import('vanilla.mail.MimeException');

class MimeRecipient
{
    const TO	= 'To';
    const CC	= 'Cc';
    const BCC	= 'Bcc';

// -------------------------------------------------------------------------->

    private $addresses; 

// -------------------------------------------------------------------------->

    public function __construct()
    {
	$this->addresses = new StringMap();
    }

// -------------------------------------------------------------------------->

    public function add(InternetAddress $addr, $recipientType)
    {
	if ( empty($addr) )
	{
	    throw new MimeException('Adress null');
	}
	
	self::checkType($recipientType);

	$list = $this->addresses->get($recipientType);
	if ( empty($list) )
	{
	    $list = new ArrayList();
	    $this->addresses->put($recipientType, $list);
	}

	$list->add($addr);
    }

    public function clear($recipientType)
    {
	self::checkType($recipientType);
	$this->addresses->put($recipientType, null);
    }

    public function getAddresses($recipientType)
    {
	self::checkType($recipientType);
	$list = $this->addresses->get($recipientType);
	if ( empty($list) )
	{
	    return null;
	}

	return $list->elements;
    }

    public function getAllAddresses()
    {
	$all = new ArrayList();
	foreach ( $this->addresses->keys() as $recipientType )
	{
	    $all->addAll( $this->addresses->get($recipientType) );
	}

	return $all->elements;
    }

// -------------------------------------------------------------------------->

    public static function checkType($recipientType)
    {
	if ( $recipientType != self::TO && $recipientType != self::CC && $recipientType != self::BCC )
	{
	    throw new MimeException('Unknown recipient type : ' . $recipientType);
	}
    }
}
?>
