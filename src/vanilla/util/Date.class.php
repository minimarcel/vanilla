<?php

import('vanilla.text.DateFormat');
import('vanilla.util.Comparable');

/**
 * A date. (Before DateTime exists)
 * A date contains only a time is seconds at GMT, and can be formated (see vanilla.text.DateFormat)
 *
 * TODO parse date
 */
class Date implements Comparable
{
    const SHORT_STYLE   = DateFormat::SHORT;
    const MEDIUM_STYLE  = DateFormat::MEDIUM;
    const LONG_STYLE    = DateFormat::LONG;
    const FULL_STYLE    = DateFormat::FULL;

// -----------------------------------> 

    private $timeInSeconds;
    
// -----------------------------------> 
    
    /**
     * Creates a new Date with the given time.
     * If the time is null, the current time will be used
     */
    public function __construct($time=null)
    {
        if ( empty($time) )
        {
            $time = time();
        }
        
        $this->timeInSeconds = $time;
    }
    
// -----------------------------------> 

    public function getTime()
    {
        return $this->timeInSeconds;
    }
    
// -----------------------------------> 

    /**
     * Format a date with the given pattern and locale.
     * The pattern will be parsed with the DateFormat class.
     * If not locale given, the default one will be used.
     *
     * @see DateFormat#getForPattern(string, vanilla.util.Locale)
     * @see DateFormat#format(vanilla.util.Date)
     */
    public function format($pattern, $locale=null)
    {
        return DateFormat::getForPattern($pattern, $locale)->format($this);
    }

    /**
     * Format a date with the given date style, time style and locale.
     * If not locale given, the default one will be used.
     *
     * @see DateFormat#getForDateTimeStyle(int, int, vanilla.util.Locale)
     * @see DateFormat#format(vanilla.util.Date)
     */
    public function style($dateStyle, $timeStyle, $locale=null)
    {
        return DateFormat::getForDateTimeStyle($dateStyle, $timeStyle, $locale)->format($this);
    }

    /**
     * Format a date with the given date style, a locale, and the default time style.
     * @see #style(int, int, vanilla.util.Locale)
     */
    public function dateStyle($dateStyle, $locale=null)
    {
        return $this->style($dateStyle, -1, $locale);
    }

    /**
     * Format a date with the given time style, a locale, and the default date style.
     * @see #style(int, int, vanilla.util.Locale)
     */
    public function timeStyle($timeStyle, $locale=null)
    {
        return $this->style(-1, $timeStyle, $locale);
    }
    
    /**
     * Returns a string representation of this date
     * @see #rfc822()
     */
    public function __toString()
    {
        return $this->rfc822();
    }

    /**
     * Returns a string representation of this date, 
     * using the rfc822 standard.
     */
    public function rfc822()
    {
        return date(DATE_RFC822, $this->timeInSeconds);
    }

    /**
     * Returns a string representation of this date, 
     * using the W3C standard.
     */
    public function w3c()
    {
        return date('c', $this->timeInSeconds);
    }
    
// -----------------------------------> 

    /**
     * Determines whether this date is before the given date.
     */
    public function isBefore(Date $date)
    {
        return ($this->timeInSeconds < $date->timeInSeconds);
    }
    
    /**
     * Determines whether this date is after the given date.
     */
    public function isAfter(Date $date)
    {
        return ($this->timeInSeconds > $date->timeInSeconds);
    }

    /**
     * Determines whether this date is equal to the given date.
     */
    public function isEquals(Date $date)
    {
        return ($this->timeInSeconds == $date->timeInSeconds);
    }

// -----------------------------------> 

    /**
     * Return -1, 0 ou 1 if this date is before, 
     * equals or after the given Comparable (date).
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

// -----------------------------------> 

    /**
     * Returns the current date
     */
    public static function current()
    {
        $date = new Date();
        return $date;
    }
}
?>
