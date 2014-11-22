<?php

import('vanilla.localisation.Localisation');
import('vanilla.util.Calendar');
import('vanilla.util.Date');
import('vanilla.util.StringMap');

/**
 *  Les dates et time utilisent les patterns de GNU :
 *    %a - nom abrégé du jour de la semaine (local)
 *    %A - nom complet du jour de la semaine (local)
 *    %b - nom abrégé du mois (local)
 *    %B - nom complet du mois (local)
 *    %d - jour du mois en numérique (intervalle 01 à 31)
 *    %e - numéro du jour du mois.
 *    %E - numéro du jour du mois avec le suffix st, nd et th
 *    %f - numéro mois (intervalle 1 à 12) (Non contenu dans GNU)
 *    %H - heure de la journée en numérique, et sur 24-heures (intervalle de 00 à 23)
 *    %I - heure de la journée en numérique, et sur 12- heures (intervalle 01 à 12)
 *    %j - jour de l'année, en numérique (intervalle 001 à 366)
 *    %k - heure de la journée en numérique, et sur 24-heures (intervalle de 0 à 23)
 *    %l - heure de la journée en numérique, et sur 12-heures (intervalle de 1 à 12)
 *    %m - mois en numérique (intervalle 01 à 12)
 *    %M - minute en numérique
 *    %n - caractère de nouvelle ligne
 *    %p - soit `am' ou `pm' en fonction de l'heure absolue (local)
 *    %S - secondes en numérique
 *    %t - tabulation
 *    %u - le numéro de jour dans la semaine, de 1 à 7. (1 représente Lundi)
 *    %U - numéro de semaine dans l'année, en considérant le premier dimanche de l'année comme le premier jour de la première semaine
 *    %V - le numéro de semaine comme défini dans l'ISO 8601:1988, sous forme décimale, de 01 à 53. La semaine 1 est la première semaine qui a plus de 4 jours dans l'année courante, et dont Lundi est le premier jour. (Utilisez %G ou %g pour les éléments de l'année qui correspondent au numéro de la semaine pour le timestamp donné.)
 *    %W - numéro de semaine dans l'année, en considérant le premier lundi de l'année comme le premier jour de la première semaine
 *    %w - jour de la semaine, numérique, avec Dimanche = 0
 *    %y - l'année, numérique, sur deux chiffres (de 00 à 99)
 *    %Y - l'année, numérique, sur quatre chiffres
 *    %Z ou %z - fuseau horaire, ou nom ou abréviation
 *    %% - un caractère `%' littéral
 *
 *  Le parsing d'une chaîne en date ne comprends que les code suivant :
 *    %a - nom abrégé du jour de la semaine (local)
 *    %A - nom complet du jour de la semaine (local)
 *    %b - nom abrégé du mois (local)
 *    %B - nom complet du mois (local)
 *    %d - jour du mois en numérique (intervalle 01 à 31)
 *    %e - numéro du jour du mois.
 *    %j - jour de l'année, en numérique (intervalle 001 à 366)
 *    %k - heure de la journée en numérique, et sur 24-heures (intervalle de 0 à 23)
 *    %l - heure de la journée en numérique, et sur 12-heures (intervalle de 1 à 12)
 *    %m - mois en numérique (intervalle 01 à 12)
 *    %f - numéro mois (intervalle 1 à 12) (Non contenu dans GNU)
 *    %p - soit `am' ou `pm' en fonction de l'heure absolue (local)
 *    %u - le numéro de jour dans la semaine, de 1 à 7. (1 représente Lundi)
 *    %w - jour de la semaine, numérique, avec Dimanche = 0
 *    %y - l'année, numérique, sur deux chiffres (de 00 à 99)
 *    %Y - l'année, numérique, sur quatre chiffres
 *    %H - heure de la journée en numérique, et sur 24-heures (intervalle de 00 à 23)
 *    %I - heure de la journée en numérique, et sur 12- heures (intervalle 01 à 12)
 *    %M - minute en numérique
 *    %S - secondes en numérique
 *    %% - un caractère `%' littéral	TODO
 *    %n - caractère de nouvelle ligne	TODO
 *    %t - caractère de tabulation	TODO
 */
class DateFormat
{
	/*
	 Styles
	*/
	const SHORT		= 0;
	const MEDIUM	= 1;
	const LONG		= 2;
	const FULL		= 3;

	//  ------------------------------------->

	/*
	 Private constantes
	*/
	const STR		= 1;	// chaîne de caractère
	const CAL		= 2;	// calendrier
	const SFT		= 3;	// méthode strftime

	//  ------------------------------------->

	private static $cache;
	private static $regularCache;
	private static $calFields = Array
	(
        'a' => Calendar::DAY_OF_WEEK, 
        'A' => Calendar::DAY_OF_WEEK,
        'b' => Calendar::MONTH,
        'B' => Calendar::MONTH,
        'd' => Calendar::DAY_OF_MONTH,
        'e' => Calendar::DAY_OF_MONTH,
		'E' => Calendar::DAY_OF_MONTH,
        'j' => Calendar::DAY_OF_YEAR,
        'k' => Calendar::HOUR,
        'l' => Calendar::HOUR,
        'm' => Calendar::MONTH,
        'f' => Calendar::MONTH,
        'p' => Calendar::HOUR,
        'u' => Calendar::DAY_OF_WEEK,
        'w' => Calendar::DAY_OF_WEEK,
        'y' => Calendar::YEAR,
        'Y' => Calendar::YEAR, 
        'H' => Calendar::HOUR, 
        'I' => Calendar::HOUR, 
        'M' => Calendar::MINUTE, 
        'S' => Calendar::SECOND 
	);

	private static $parsingPatterns = Array
	(
	'a' => "\S+.?", 
	'A' => "\S+", 
	'b' => "\S+.?", 
	'B' => "\S+", 
	'd' => "[0-9]{2}", 
	'e' => "[0-9]{1,2}", 
	'f' => "[0-9]{1,2}", 
	'H' => "[0-9]{2}", 
	'I' => "[0-9]{2}", 
	'I' => "[0-9]{2}", 
	'j' => "[0-9]{3}", 
	'k' => "[0-9]{1,2}", 
	'l' => "[0-9]{1,2}", 
	'm' => "[0-9]{2}", 
	'M' => "[0-9]{2}", 
	'p' => "\S+", 
	'S' => "[0-9]{2}", 
	'u' => "[1-7]{1}", 
	'w' => "[0-6]{1}", 
	'y' => "[0-9]{2}", 
	'Y' => "[0-9]{4}"
	);

	//  ------------------------------------->

	private $calendar;
	private $pattern;
	private $locale;

	private $compiledPattern;
	private $regularExpression;

	//  ------------------------------------->

	private function __construct($pattern, VLocale $locale)
	{
		$this->pattern	= $pattern;
		$this->locale	= $locale;
		$this->calendar	= Calendar::getInstance($locale);

		$this->compile();
		$this->createRegularExpression();
	}

	//  ------------------------------------->

	public function format(Date $date)
	{
		$this->calendar->setTime($date);
		$l = sizeof($this->compiledPattern);
		$s = "";

		for ( $i = 0 ; $i < $l ; )
		{
			$t = $this->compiledPattern[$i++];
			$v = $this->compiledPattern[$i++];

			switch ( $t )
			{
				// string
				case self::STR :
				{
		    		$s .= $v;
		    		break;
				}

				// utilsation de la méthode strftime
				case self::SFT :
				{
		    		$s .= strftime($v, $date->getTime());
		    		break;
				}

					// utilisation du calendrier
				case self::CAL :
				{
		    		$s .= $this->calPattern($v);
				}
			}
		}

		return $s;
	}

	public function parse($s)
	{
		if ( empty($s) )
		{
			throw new Exception("Can't parse date : string empty");
		}
		
		if ( mb_ereg($this->regularExpression, $s, $matches) < 1 )
		{
			throw new Exception("Can't parse date : pattern do not match");
		}

		$this->calendar->setTime(new Date());

		$fields = Array();
		$amPm	= -1;

		$l = sizeof($this->compiledPattern);
		$i = 1;

		/*
			Récupération des informations
		*/

		for ( $j = 0 ; $j < $l ; )
		{
			$t = $this->compiledPattern[$j++];
			$v = $this->compiledPattern[$j++];

			if ( $t != self::CAL )
			{
				continue;
			}

			$m = $matches[$i++];
			$f = self::$calFields[$v];

			if ( $v == 'p' )
			{
				if ( $amPm > -1 )
				{
					continue;
				}

				$a = $this->locale->getProperties('AmPmMarkers');
				if ( empty($a) )
				{
					$a = Array('AM', 'PM');
				}

				$amPm = $this->searchValue($a, $m);
			}
			else if ( isset($fields[$f]) )
			{
				continue;
			}
			else
			{
				$fields[$f] = $this->calMatch($v, $m);
			}
		}

		if ( isset($fields[Calendar::HOUR]) && $amPm > 0 )
		{
			$fields[Calendar::HOUR] += 12;
		}

		/*
			Création de la date
		*/

		$cal = $this->calendar->duplicate();

		$cal->clearTimeInformations();
		$cal->set(Calendar::DAY_OF_MONTH, 1);
		$cal->set(Calendar::MONTH, Calendar::JANUARY);

		$parentSetted = false;

		// année
		$this->setField($cal, $fields, Calendar::YEAR, $parentSetted);

		if ( isset($fields[Calendar::DAY_OF_YEAR]) )
		{
			$cal->set(Calendar::DAY_OF_YEAR, $fields[Calendar::DAY_OF_YEAR]);
		}
		else
		{
			// mois
			$this->setField($cal, $fields, Calendar::MONTH, $parentSetted);

			// jour du mois
			if ( isset($fields[Calendar::DAY_OF_MONTH]) )
			{
				$parentSetted = true;
				$cal->set(Calendar::DAY_OF_MONTH, $fields[Calendar::DAY_OF_MONTH]);
			}
			else if ( !$parentSetted )
			{
				// valeur courrante
				$cal->set(Calendar::DAY_OF_MONTH, $this->calendar->get(Calendar::DAY_OF_MONTH));

				// jour de la semaine
				if ( isset($fields[Calendar::DAY_OF_WEEK]) )
				{
					$parentSetted = true;
					$cal->set(Calendar::DAY_OF_WEEK, $fields[Calendar::DAY_OF_WEEK]);
				}
			}
		}

		// heure
		$this->setField($cal, $fields, Calendar::HOUR, $parentSetted);

		// minutes
		$this->setField($cal, $fields, Calendar::MINUTE, $parentSetted);

		// secondes
		$this->setField($cal, $fields, Calendar::SECOND, $parentSetted);

		return $cal->getTime();
	}

	private function setField($cal, $fields, $field, &$parentSetted)
	{
		if ( isset($fields[$field]) )
		{
			$parentSetted = true;
			$cal->set($field, $fields[$field]);
		}
		else if ( !$parentSetted )
		{
			// valeur courrante
			$cal->set($field, $this->calendar->get($field));
		}
	}

	//  ------------------------------------->

	private function calPattern($p)
	{
		$v = $this->calendar->get( self::$calFields[$p] );

		switch ( $p )
		{
			case 'a' :
			{
				$a = $this->locale->getProperties('DayAbreviations');
				return trim($a[$v]);
			}

			case 'A' :
			{
				$a = $this->locale->getProperties('DayNames');
				return trim($a[$v]);
			}

			case 'b' :
			{
				$a = $this->locale->getProperties('MonthAbreviations');
				return trim($a[$v - 1]);
			}

			case 'B' :
			{
				$a = $this->locale->getProperties('MonthNames');
				return trim($a[$v - 1]);
			}

			case 'j' :
			{
				return $this->zeroPaddingNumber($v + 1, 3);
			}

			case 'p' :
			{
				$a = $this->locale->getProperties('AmPmMarkers');
				if ( empty($a) )
				{
	    			return ($v < 12 ? "AM" : "PM");
				}
				else
				{
	    			return ($v < 12 ? trim($a[0]) : trim($a[1]));
				}
			}

			case 'u' :
			{
				return ($v == 0 ? 7 : $v);
			}
			
			case 'k' :
			case 'f' :
			case 'e' :
			case 'w' :
			{
				return $v;
			}
			
			case 'E' : 
			{
				$suffix = "th";
				switch($v)
				{
					case 1 : $suffix = 'st'; break;
					case 2 : $suffix = 'nd'; break;
					case 3 : $suffix = 'rd'; break;
				}
				
				return "$v$suffix";	
			}
				 
			case 'y' :
			{
				$y = $this->zeroPaddingNumber($v, 2);
				return substr($y, -2);
			}

			case 'Y' :
			{
				return $this->zeroPaddingNumber($v, 4);
			}

			case 'l' :
			{
				return $this->to12Hour($v);
			}

			case 'I' :
			{
				return $this->zeroPaddingNumber($this->to12Hour($v), 2);
			}

			case 'd' :
			case 'm' :
			case 'H' :
			case 'M' :
			case 'S' :
			{
				return $this->zeroPaddingNumber($v, 2);
			}
		}
	}

	private function to12Hour($v)
	{
		if ( $v == 0 )
		{
			return 12;
		}

		return ($v > 12 ? $v - 12 : $v);
	}

	private function zeroPaddingNumber($v, $nb)
	{
		// TODO utiliser le format des nombres
		$s = "$v";
		$l = $nb - strlen($s);
		for ( $i = 0 ; $i < $l ; $i++ )
		{
			$s = "0$s";
		}

		return $s;
	}

	private function calMatch($v, $m)
	{
		switch($v)
		{
			case 'a' :
			{
				return $this->searchValue($this->locale->getProperties('DayAbreviations'), $m);
			}

			case 'A' :
			{
				return $this->searchValue($this->locale->getProperties('DayNames'), $m);
			}

			case 'b' :
			{
				return $this->searchValue($this->locale->getProperties('MonthAbreviations'), $m) + 1;
			}

			case 'B' :
			{
				return $this->searchValue($this->locale->getProperties('MonthNames'), $m) + 1;
			}

			case 'j' :
			{
				return intval($m) - 1;
			}

			case 'u' :
			{
				// jour de la semaine
				return intval($m)%7;
			}

			case 'I' :
			case 'l' :
			{
				// heure sur 12 heures
				return intval($m)%12;
			}

			case 'y' :
			{
				// annee sur 2 ans
				$y4 = $this->calendar->get(Calendar::YEAR);
				$y2 = $this->zeroPaddingNumber($y4, 2);
				$y2 = intval(substr($y2, -2));
				$y4 = $y4 - $y2;
				$y2 = $y2 / 10 * 10 + 10;

				$m = intval($m);
				return ( $m < $y ? $m + $y4 : $m + ($y4-100) );
			}

			case 'k' :
			case 'f' :
			case 'e' :
			case 'w' :
			case 'Y' :
			case 'd' :
			case 'm' :
			case 'H' :
			case 'M' :
			case 'S' :
			{
				return intval($m);
			}
				 
		}
	}

	private function searchValue($array, $v)
	{
		$v = mb_strtolower(trim($v));
		$i = 0;
		foreach ( $array as $val )
		{
			if ( mb_strtolower(trim($val)) == $v )
			{
				return $i;
			}

			$i++;
		}

		throw new Exception("Unknown string : $v");
	}

	//  ------------------------------------->

	private function compile()
	{
		if ( isset(self::$cache))
		{
			$this->compiledPattern = self::$cache->get($this->pattern);
			if ( !empty($this->compiledPattern) )
			{
				return;
			}
		}
		else
		{
			self::$cache = new StringMap();
		}

		$this->compiledPattern = Array();

		$l = strlen($this->pattern);
		$b = 0; // begin of pattern
		$s = self::STR; // current state
		$o = self::STR; // old state
		$i = 0; // last index
		$p = 0; // position of $

		while ( ($p = strpos($this->pattern, '%', $i)) !== false )
		{
			if ( ($p+1) >= $l )
			{
				throw new Exception("Invalid end of pattern");
				break;
			}

			$c = $this->pattern[($p+1)];
			switch($c)
			{
				/*
		  		 Patterns gérés par le calendrier
				*/
				case 'a' :
				case 'A' :
				case 'b' :
				case 'B' :
				case 'd' :
				case 'E' :
				case 'e' :
				case 'f' :
				case 'j' :
				case 'k' :
				case 'l' :
				case 'm' :
				case 'p' :
				case 'u' :
				case 'w' :
				case 'y' :
				case 'Y' :
				case 'H' :
				case 'I' :
				case 'M' :
				case 'S' :
				{
			    	$s = self::CAL;
			    	break;
				}

				/*
				 Patterns gérés par la méthode strftime
				*/
				case 'n' :
				case 't' :
				case 'U' :
				case 'V' :
				case 'W' :
				case 'Z' :
				case 'z' :
				case '%' :
				{
				    $s = self::SFT;
				    break;
				}

				default :
				{
		    		throw new Exception("Unknwon char : %$c");
				}
			}

			if ( $s == self::CAL )
			{
				// on gère le cas précédent
				if ( ($p - $b) > 0 )
				{
					$this->compiledPattern[] = $o;
					$this->compiledPattern[] = substr($this->pattern, $b, $p-$b);
				}

				// on enregistre ce pattern
				$this->compiledPattern[] = $s;
				$this->compiledPattern[] = $c;

				// on prépare pour le pattern suivant
				$o = self::STR;
				$b = $p+2;
			}
			else if ( $s != $o )
			{
				if ( ($p - $b) > 0 )
				{
					$this->compiledPattern[] = $o;
					$this->compiledPattern[] = substr($this->pattern, $b, $p-$b);
				}
					
				// on prépare pour le pattern suivant
				$o = $s;
				$b = $p;
			}

			$i = $p+2;
		}

		// on gère le dernier cas
		if ( ($l - $b) > 0 )
		{
			$this->compiledPattern[] = $o;
			$this->compiledPattern[] = substr($this->pattern, $b);
		}

		// on le rajoute dans le cache
		self::$cache->put($this->pattern, $this->compiledPattern);
	}

	private function createRegularExpression()
	{
		if ( isset(self::$regularCache))
		{
			$this->regularExpression = self::$regularCache->get($this->pattern);
			if ( !empty($this->regularExpression) )
			{
				return;
			}
		}
		else
		{
			self::$regularCache = new StringMap();
		}

		$l = sizeof($this->compiledPattern);
		$s = "";

		for ( $i = 0 ; $i < $l ; )
		{
			$t = $this->compiledPattern[$i++];
			$v = $this->compiledPattern[$i++];

			switch ( $t )
			{
				// string ou strftime (not supported for now)
				case self::STR :
				case self::SFT :
				{
		 		   $s .= $v; //FIXME escape
		    		break;
				}

				// utilisation du calendrier
				case self::CAL :
				{
		    		$s .= "(" . self::$parsingPatterns[$v] . ")";
				}
			}
		}

		$this->regularExpression = "^$s$";

		// on le rajoute dans le cache
		self::$regularCache->put($this->pattern, $this->regularExpression);
	}

	//  ------------------------------------->

	public function getLocale()
	{
		return $this->locale;
	}

	public function getPattern()
	{
		return $this->pattern;
	}

	//  ------------------------------------->

	public static function getForPattern($pattern, $locale=null)
	{
		if ( empty($locale) )
		{
			$locale = Localisation::getCurrentLocale();
		}

		return new DateFormat($pattern, $locale);
	}

	public static function getForDateTimeStyle($dateStyle, $timeStyle, $locale=null)
	{
		if ( empty($locale) )
		{
			$locale = Localisation::getCurrentLocale();
		}

		$properties = $locale->getProperties('DateTimePatterns');
		if ( empty($properties) )
		{
			throw new Exception('Can\'t find locale date time patterns');
		}

		$date	= null;
		$time	= null;
		$result = null;

		if ( $dateStyle >= 0 )
		{
			$date   = trim($properties[$dateStyle]);
			$result = $date;
		}

		if ( $timeStyle >= 0 )
		{
			$time   = trim($properties[$timeStyle + 4]);
			$result = $time;
		}

		if ( $timeStyle >= 0 && $dateStyle >= 0 )
		{
			eval('$result = "' . trim($properties[8]) . '";');
		}

		return self::getForPattern($result, $locale);
	}
}
?>
