<?php

import('vanilla.util.Date');
import('vanilla.localisation.VLocale');

/**
 * A calendar is desinged to manipulate dates.
 * A locale is provided to the calendar, in order to select the right one, 
 * but the only implementation provided (for now) is the Gregorian calendar.
 *
 * Becareful : 
 * - the first day of a week is sunday, and starts at 0
 * - the first day of a year starts at the number 0
 */
class Calendar
{
    const SECOND        = "seconds";
    const MINUTE        = "minutes";
    const HOUR          = "hours";
    const DAY_OF_MONTH  = "mday";
    const DAY_OF_WEEK   = "wday";
    const DAY_OF_YEAR   = "yday";
    const MONTH         = "mon";
    const YEAR          = "year";

// -----------------------------------> 

    const JANUARY   = 1;
    const FEBRUARY  = 2;
    const MARCH     = 3;
    const APRIL     = 4;
    const MAY       = 5;
    const JUNE      = 6;
    const JULY      = 7;
    const AUGUST    = 8;
    const SEPTEMBER = 9;
    const OCTOBER   = 10;
    const NOVEMBER  = 11;
    const DECEMBER  = 12;

    const SUNDAY    = 0;
    const MONDAY    = 1;
    const THURSDAY  = 2;
    const WEDNESDAY = 3;
    const TUESDAY   = 4;
    const FRIDAY    = 5;
    const SATURDAY  = 6;

// -----------------------------------> 

    private $timeArray;
    private $timeArrayGMT;
    private $locale;

// -----------------------------------> 

    /**
     * Creates a new calendar with the given vanilla locale
     */
    private function __construct(VLocale $locale)
    {
        $this->locale = $locale;
        $this->setTimeInSeconds( time() );
    }

// -----------------------------------> 

    /**
     * Set the time
     */
    public function setTime(Date $time)
    {
        $this->setTimeInSeconds( $time->getTime() );
    }

    /**
     * Set the time in seconds
     */
    public function setTimeInSeconds($time)
    {
        $this->timeArray = getdate($time);
        
        $oldTimeZone = date_default_timezone_get();
        date_default_timezone_set('GMT');
        
        $this->timeArrayGMT = getdate($time);
        date_default_timezone_set($oldTimeZone);
    }

    /**
     * Returns the time (a date)
     */
    public function getTime()
    {
        $date = new Date( $this->getTimeInSeconds() );
        return $date;
    }

    /**
     * Returns the time in seconds
     */
    public function getTimeInSeconds()
    {
        return $this->timeArray[0];
    }

    /**
     * Get the locale that is attached to this calendar.
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Clear any time relative information, meaning : 
     *  hour, minute, second
     */
    public function clearTimeInformations()
    {
        $this->set(self::SECOND,    0);
        $this->set(self::MINUTE,    0);
        $this->set(self::HOUR,      0);
    }

    /**
     * Returns a new instance that is a copy of this calendar
     */
    public function duplicate()
    {
        $calendar = self::getInstance($this->locale);
        $calendar->timeArray = $this->timeArray;

        return $calendar;
    }

// -----------------------------------> 

    /**
     * Returns the value of the given field.
     * The accepted fields are the constants :
     *  - SECOND
     *  - MINUTE
     *  - HOUR
     *  - DAY_OF_MONTH
     *  - DAY_OF_WEEK
     *  - DAY_OF_YEAR
     *  - MONTH
     *  - YEAR
     */ 
    public function get($field)
    {
        return $this->timeArray[$field];
    }

    /**
     * Set a value for the given field.
     * The accepted list of constants accepted for the parameter field are described on the method #get(field).
     *
     * If the given value overlaps, the calendar will always translate the time to fallback on an correct date : 
     * for exemple if you give 32 for the january month, the calendar will translate into the 1 of february of the same year.
     */ 
    public function set($field, $value)
    {
        if ( $field == self::DAY_OF_WEEK || $field == self::DAY_OF_YEAR )
        {
            $diff = $value - $this->get($field);
            return $this->add(self::DAY_OF_MONTH, $diff);
        }

        $t = $this->timeArray;
        $t[$field] = $value;
        $this->updateTime($t);
    }

    /**
     * Add the given amount to the given field.
     * The amount can be positive or negative.
     * The accepted list of constants accepted for the parameter field are described on the method #get(field).
     */
    public function add($field, $value)
    {
        if ( $field == self::DAY_OF_WEEK || $field == self::DAY_OF_YEAR )
        {
            $field = self::DAY_OF_MONTH;
        }

        $t = $this->timeArrayGMT;
        $t[$field] += $value;
        $this->updateGMTTime($t);
    }

    private function updateTime($t)
    {
        $time = mktime($t[self::HOUR], $t[self::MINUTE], $t[self::SECOND],
                $t[self::MONTH], $t[self::DAY_OF_MONTH], $t[self::YEAR]);

        $this->setTimeInSeconds($time);
    }
    
    private function updateGMTTime($t)
    {
        $time = gmmktime($t[self::HOUR], $t[self::MINUTE], $t[self::SECOND],
                $t[self::MONTH], $t[self::DAY_OF_MONTH], $t[self::YEAR]);
    
        $this->setTimeInSeconds($time);
    }

//------------------------------------->

    public function __toString()
    {
        $s = '';
        $s = $s . 'SECOND['         . $this->get(self::SECOND)          . '] ';
        $s = $s . 'MINUTE['         . $this->get(self::MINUTE)          . '] ';
        $s = $s . 'HOURS['          . $this->get(self::HOUR)            . '] ';
        $s = $s . 'DAY_OF_MONTH['   . $this->get(self::DAY_OF_MONTH)    . '] ';
        $s = $s . 'DAY_OF_WEEK['    . $this->get(self::DAY_OF_WEEK)     . '] ';
        $s = $s . 'DAY_OF_YEAR['    . $this->get(self::DAY_OF_YEAR)     . '] ';
        $s = $s . 'MONTH['          . $this->get(self::MONTH)           . '] ';
        $s = $s . 'YEAR['           . $this->get(self::YEAR)            . '] ';
        $s = $s . 'TIME['           . $this->getTimeInSeconds()         . '] ';
        $s = $s . 'DATE['           . $this->getTime()->__toString()    . '] ';
      
        return $s;
    }

//------------------------------------->

    /**
     * Return an instance for the current locale.
     */
    public static function getInstance($locale=null)
    {
        if ( empty($locale) )
        {
            $locale = Localisation::getCurrentLocale();
        }

        // TODO BuddhistCalendar for Thai locale
        return new Calendar($locale);
    }
}
?>
