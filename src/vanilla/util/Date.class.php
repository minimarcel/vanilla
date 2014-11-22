<?php

import('vanilla.text.DateFormat');
import('vanilla.util.Comparable');

/**
 */
class Date implements Comparable
{
    const SHORT_STYLE	= DateFormat::SHORT;
    const MEDIUM_STYLE	= DateFormat::MEDIUM;
    const LONG_STYLE	= DateFormat::LONG;
    const FULL_STYLE	= DateFormat::FULL;

//------------------------------------->

    private $timeInSeconds;
    
//------------------------------------->
    
    public function __construct($time=null)
    {
	if ( empty($time) )
	{
	    $time = time();
	}
	
	$this->timeInSeconds = $time;
    }
    
//------------------------------------->

    public function getTime()
    {
	return $this->timeInSeconds;
    }
    
//------------------------------------->

    public function format($pattern, $locale=null)
    {
	return DateFormat::getForPattern($pattern, $locale)->format($this);
    }

    public function style($dateStyle, $timeStyle, $locale=null)
    {
	return DateFormat::getForDateTimeStyle($dateStyle, $timeStyle, $locale)->format($this);
    }

    public function dateStyle($dateStyle, $locale=null)
    {
	return $this->style($dateStyle, -1, $locale);
    }

    public function timeStyle($timeStyle, $locale=null)
    {
	return $this->style(-1, $timeStyle, $locale);
    }
    
    // TODO
    //function static parse($format)
    // create a DateFormat class, qui parse un patern
    // la fonction static pourrait utiliser ça par défaut 
    // mais si on veut optimiser on utilise la class DateFormat par défaut
    // on a aussie strptime mais que ne marche que sous Linux
	    
    public function __toString()
    {
	return $this->rfc822();
    }

    public function rfc822()
    {
	return date(DATE_RFC822, $this->timeInSeconds);
    }

    public function w3c()
    {
	return date('c', $this->timeInSeconds);
    }
    
//------------------------------------->

    /**
     * Determines whether the date represented by this object
     * is before the given date.
     */
    public function isBefore(Date $date)
    {
	return ($this->timeInSeconds < $date->timeInSeconds);
    }
    
    /**
     * Determines whether the date represented by this object
     * is after the given date.
     */
    public function isAfter(Date $date)
    {
	return ($this->timeInSeconds > $date->timeInSeconds);
    }

    /**
     * Determines whether the date represented by this object
     * is equals the given date.
     */
    public function isEquals(Date $date)
    {
	return ($this->timeInSeconds == $date->timeInSeconds);
    }

//------------------------------------->

    /**
     * Retourne -1, 0 ou 1 si cet objet est inférieur, 
     * égal ou supérieur à l'objet donné
     */
    public function compareTo(Comparable $o)
    {
	if ( $o instanceof Date )
	{
	    if ( $this->isEquals($o) )
	    {
		return 0;
	    }
	    else if ( $this->isAfter($o) )
	    {
		return 1;
	    }
	}

	return -1;
    }

//------------------------------------->

    public static function current()
    {
	$date = new Date();
	return $date;
    }
}
?>
