<?php

/**
 * 
 */
class InternetAddressException extends Exception
{
    private $address;
    private $index;

// -------------------------------------------------------------------------->

    public function __construct($message, $address, $index)
    {
	parent::__construct($message, 0);
	$this->address = $address;
	$this->index = $index;
    } 
}
?>
