<?php

import('vanilla.runtime.HumanReadable');
import('vanilla.runtime.CodifiedException');

/**
 * 
 */
class HumanReadableException extends CodifiedException implements HumanReadable
{
    /**
     * Construit une exception lisible par un humain
     * 
     * @param	message	    le message lisible
     * @param	code	    un code identifiant cette exception
     */
    public function __construct($message, $code=null)
    {
	parent::__construct($message, $code);
    } 
}
?>
