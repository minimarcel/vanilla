<?php

if ( class_exists('Locale') )
{
	die("** The intl extension is activated, use VLocale instead **\n");
	return;
}

import('vanilla.localisation.VLocale');


/**
 * The old locale, deprecated, replaced by VLocale
 */
class Locale extends VLocale
{
    public function __construct($language, $country)
    {
	    parent::__construct($language, $country);
    }
}
?>
