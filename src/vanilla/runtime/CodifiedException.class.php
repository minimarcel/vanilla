<?php

/**
 * 
 */
class CodifiedException extends Exception 
{
    protected $stringCode;

//  ----------------------------------------->

    /**
     * Construit une exception lisible par un humain
     * 
     * @param	message	    le message lisible
     * @param	code	    un code identifiant cette exception
     */
    public function __construct($message, $code=null)
    {
	parent::__construct($message, is_long($code) ? $code : null);
	$this->stringCode = $code;
    } 

//  ----------------------------------------->

    public function getStringCode()
    {
	return empty($this->stringCode) ? $this->getCode() : $this->stringCode;
    }
}
?>
