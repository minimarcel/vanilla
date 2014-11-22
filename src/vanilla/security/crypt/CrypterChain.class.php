<?php

import('vanilla.security.crypt.Crypter');

/**
 * Improved vigenere crypter
 * Se base sur un encodage en base64 pour encoder la chaîne puis la crypt avec
 * un vigenere amélioré.
 */
class CrypterChain implements Crypter
{
    private $chain;

//  ---------------------------------------------------------------------->

    public function __construct() 
    {
	$this->chain = new ArrayList();
    }

//  ---------------------------------------------------------------------->

    public function encrypt($s)
    {
	$l = $this->chain->size();
	for ( $i = 0 ; $i < $l ; $i++ )
	{
	    $s = $this->chain->get($i)->encrypt($s);
	}

	return $s;
    }

    public function decrypt($s)
    {
	$l = $this->chain->size();
	for ( $i = $l - 1 ; $i >= 0 ; $i-- )
	{
	    $s = $this->chain->get($i)->decrypt($s);
	}

	return $s;
    }

//  ---------------------------------------------------------------------->

    public function append(Crypter $crypter)
    {
	$this->chain->add($crypter);
	return $this;
    }
}
?>
