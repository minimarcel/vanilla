<?php

import('vanilla.localisation.Localisation');
import('vanilla.text.NumberFormatSymbols');
import('vanilla.text.json.VJSONSerializable');

/**
 *
 *  #.##0,##;-#.##0,##
 *  %#.##0,##%;%-%#.##0,##%
 *  
 *  #.##0,##;(#.##0,##)
 *  %#.##0,##%;(%#.##0,##%)
 *  
 *  #.##0,00 $;-#.##0,00 $
 *  %#.##0,00% %c$;%-%#.##0,00% %c
 *  
 *  
 *  En gros on met dans le pattern
 *      entre %...% le nombre
 *      %c la currency
 *      %- le signe moins
 *      %p le signe pourcent
 *      %m le signe pourmille
 *      %% le caractère pourcent
 *      ;  le séparateur 
 *      ;; le caractère ;
 *  
 *  Dans le nombre on met : 
 *      # un digit
 *      0 un zéro
 *      E un exposent
 *      , le séparateur de groupes
 *      . le séparateur des décimales
 *  
 *  bon dans un premier temps on ne va pas gérer l'exposant je pense
 */
class NumberFormat implements VJSONSerializable
{
	/*
	 Separators
	*/
	const PATTERN_SEPARATOR		= ';';
	const GROUPING_SEPARATOR	= ',';
	const DECIMAL_SEPARATOR		= '.';

	/*
	 Styles
	*/
	const INTEGER_STYLE		= -1;
	const DECIMAL_STYLE		=  0;
	const CURRENCY_STYLE	=  1;
	const PERCENT_STYLE		=  2;

	/*
	 Private constantes
	*/
	const STR		= 1;	// chaîne de caractère
	const SPE		= 2;	// caractère spécial (%%) ou localisé (%c, %p, %m)
	const NUM		= 3;	// le nombre
	 

	//  ------------------------------------->

	private static $cache;
	private static $regularCache;

	//  ------------------------------------->

	private $currency	= null; // ?
	private $symbols	= null;

	private $decimalSeparatorAlwaysShown=false;
	private $groupingSize=3;
	private $groupingUsed=true;

	// min and max
	private $maximumFractionDigits=3; // -1 means infinite
	private $minimumFractionDigits=0;
	private $maximumIntegerDigits=-1; // -1 means infinite
	private $minimumIntegerDigits=1;

	// multiplie the value before formating
	private $multiplier = 1;

	private $compiledPositivePattern=null;
	private $compiledNegativePattern=null;
	
	private $numberRegularExpression=null;
	
	//  ------------------------------------->

	private function __construct($pattern, VLocale $locale)
	{
		$this->symbols = new NumberFormatSymbols($locale);
		$this->compile($pattern);
	}

	//  ------------------------------------->

	private function compile($pattern)
	{
		// dans un premier temps on cherche les deux patterns
		list($positivePattern, $negativePattern) = $this->explodePattern($pattern);

		if ( empty($positivePattern) )
		{
			throw new Exception("No positive pattern");
		}
		
		/*
		 On récupère les patterns dans le cache
		*/

		if ( isset(self::$cache))
		{
			$this->compiledPositivePattern = self::$cache->get($positivePattern);
			if ( !empty($negativePattern) )
			{
				$this->compiledNegativePattern = self::$cache->get($negativePattern);
			}
		}
		else
		{
			self::$cache = new StringMap();
		}

		/*
		 On compile les patterns et on les mets en cache
		*/

		if ( empty($this->compiledPositivePattern) )
		{
			$this->compiledPositivePattern = $this->compilePattern($positivePattern, true);
			self::$cache->put($positivePattern, $this->compiledPositivePattern);
		}

		if ( !empty($negativePattern) && empty($this->compiledNegativePattern) )
		{
			$this->compiledNegativePattern = $this->compilePattern($negativePattern, false);
			self::$cache->put($negativePattern, $this->compiledNegativePattern);
		}

		/*
		 On extrait les informations concernant le nombre
		*/

		$l = sizeof($this->compiledPositivePattern);
		for ( $i = 0 ; $i < $l ; $i++ )
		{
			if ( $this->compiledPositivePattern[$i] == self::NUM )
			{
				$p = $this->compiledPositivePattern[$i+1];

				$this->decimalSeparatorAlwaysShown  = $p->decimalSeparatorAlwaysShown;
				$this->groupingSize		    		= $p->groupingSize;
				$this->groupingUsed		    		= $p->groupingUsed;
				$this->maximumFractionDigits	    = $p->maximumFractionDigits;
				$this->minimumFractionDigits	    = $p->minimumFractionDigits;
				$this->minimumIntegerDigits	    	= $p->minimumIntegerDigits;
				break;
			}
		}
		
		/*
		 * Calcul de la regular expression du nombre pour le positive pattern
		 */
		
		if ( !isset(self::$regularCache) )
		{
			self::$regularCache = new StringMap();
		}
		else
		{
			$this->numberRegularExpression = self::$regularCache->get($positivePattern);
			if ( !empty($this->numberRegularExpression) )
			{
				return;
			}
		}
		
		$this->numberRegularExpression = $this->generateRegularExpression();
		self::$regularCache->put($positivePattern, $this->numberRegularExpression);
	}
	
	private function explodePattern($pattern)
	{
		$pos = strpos($pattern, self::PATTERN_SEPARATOR);
		$l = strlen($pattern) - 1;
		while ( $pos !== FALSE )
		{
			if ( $pos < $l && $pattern[$pos+1] != ';' )
			{
				return Array(substr($pattern, 0, $pos), substr($pattern, $pos+1));;
			}
		}

		return Array($pattern, null);
	}

	private function compilePattern($pattern, $compileNumber)
	{
		/*
		 On fait une première passe pour compiler le pattern
		*/

		$compiledPattern = Array();

		$l = strlen($pattern);
		$b = 0; // begin of pattern
		$s = self::STR; // current state
		$i = 0; // last index
		$p = 0; // position of $
		$n = -1; // la position du nombre dans les patterns

		while ( ($p = strpos($pattern, '%', $i)) !== false )
		{
			if ( $s == self::NUM )
			{
				/*
		   			le % définit la fin du nombre
				*/

				$o = new Object();
				$o->pattern = substr($pattern, $b, $p-$b);

				$compiledPattern[] = $o;

				$s = self::STR;
				$b = $p+1;
				$i = $b;

				continue;
			}

			/*
				Nouveau %
			*/

			if ( ($p+1) >= $l )
			{
				throw new Exception("Invalid end of pattern in $pattern");
				break;
			}

			$c = $pattern[($p+1)];
			switch($c)
			{
				/*
					Un nombre
				*/
				case '#' :
				case '0' :
				case '.' : $s = self::NUM; break;
				 
				/*
					Un caractère localisable
					ou un pourcent
				*/
				case '-' : // minus
				case 'c' : // currency
				case 'p' : // percent
				case 'm' : // permill
				case 'E' : // exponent
				case 'i' : // infinity
				case '%' : $s = self::SPE; break;

				default : throw new Exception("Unkown char : %$c");
			}

			if ( ($p - $b) > 0 )
			{
				$compiledPattern[] = self::STR;
				$compiledPattern[] = substr($pattern, $b, $p-$b);
			}

			if ( $s == self::NUM )
			{
				if ( $n > -1 )
				{
					throw new Exception("Only one number section is accepted : $pattern");
				}

				$compiledPattern[] = $s;
				$n = sizeof($compiledPattern);
				$b = $p+1;
				$i = $b;
			}
			else
			{
				$compiledPattern[] = $s;
				$compiledPattern[] = $c;

				$b = $p+2;
				$i = $b;
				$s = self::STR;
			}
		}

		if ( $s == self::NUM )
		{
			throw new Exception("Unclosed % for number in $pattern");
		}
		else if ( $s == self::STR && ($l - $b) > 0 )
		{
			$compiledPattern[] = self::STR;
			$compiledPattern[] = substr($pattern, $b, $l-$b);
		}

		/*
		 La deuxième passe compile le nombre
		*/

		if ( !$compileNumber )
		{
			return $compiledPattern;
		}

		if ( $n	< 0 )
		{
			throw new Exception("No number in pattern $pattern");
		}

		if ( $n >= sizeof($compiledPattern) )
		{
			throw new Exception("Unclosed % for number in $pattern");
		}

		$p = $compiledPattern[$n];
		$s = $compiledPattern[$n]->pattern;
		$l = strlen($s);

		if ( $l <= 0 )
		{
			throw new Exception("Empty number in pattern $pattern");
		}

		/**
		 * On initialise les proprietes
		 */

		$ndi	=  0;   // nombre de digits pour la partie entière
		$nzi	=  0;   // nombre de zéro pour la partie entière
		$ndd	=  0;   // nombre de digits pour la partie décimale
		$nzd	=  0;   // nombre de zéro pour la partie décimale
		$gp	= -1;   // le grouping char index
		$dp	= -1;   // le decimal char index

		for ( $i = 0 ; $i < $l ; $i++ )
		{
			$c = $s[$i];
			switch ( $c )
			{
				case '#' :
				{
				    if ( $dp < 0 )
				    {
				    	$ndi++;
				    	if ( $nzi > 0 )
				    	{
				    		throw new Exception("Unexcepted # at pos $i in $s");
				    	}
				    }
				    else
				    {
				    	$ndd++;
				    }
				}
				break;

				case '0' :
				{
				    if ( $dp < 0 )
				    {
				    	$nzi++;
				    }
				    else
				    {
				    	$nzd++;
				    	if ( $ndd > 0 )
				    	{
				    		throw new Exception("Unexcepted 0 at pos $i in $s");
				    	}
				    }
				}
				break;

				case '.' :
				{
				    if ( $dp >= 0 )
				    {
				    	throw new Exception("Unexcepted . at pos $i in $s");
				    }
				    $dp = $i;
				}
				break;

				case ',' :
				{
				    if ( $dp >= 0 )
				    {
				    	throw new Exception("Unexcepted , at pos $i in $s");
				    }
				    $gp = $i;
				}
				break;

				default	 : throw new Exception("Invalid character [$c] in number pattern $s");
			}
		}

		if ( $gp > 0 )
		{
			$p->groupingSize = ($dp >=0 ? $dp : $i) - $gp - 1;
			$p->groupingUsed = ($p->groupingSize > 0);
		}
		else
		{
			$p->groupingSize = 0;
			$p->groupingUsed = false;
		}

		$p->minimumIntegerDigits	= $nzi;
		$p->minimumFractionDigits	= $nzd;
		$p->maximumFractionDigits	= $nzd + $ndd;
		$p->decimalSeparatorAlwaysShown	= ($dp == 0) || ($dp == $i - 1);

		return $compiledPattern;
	}
	
	private function generateRegularExpression()
	{
		/*
		 * On construit le pattern du nombre
		 */
		
		$numberPattern = "";
		
		// int
		$ip = "[0-9]";
		if ( $this->maximumIntegerDigits > 0 )
		{
			$ip .= "{" . $this->minimumIntegerDigits . "," . $this->maximumIntegerDigits . "}+";
		}
		else if ( $this->minimumIntegerDigits == 0 )
		{
			$ip .= "*";
		}
		else
		{
			$ip .= "+";
		}
		
		if ( $this->groupingUsed )
		{
			$numberPattern .= "($ip(,[0-9]{" . $this->groupingSize . "})*)";
		}
		else
		{
			$numberPattern = "($ip)";
		}

		// dec
		$dp = "[0-9]";
		if ( !$this->decimalSeparatorAlwaysShown )
		{
			// TODO
			$dp = "\.$dp";
		}
		else
		{
			$numberPattern .= "\.";
		}

		if ( $this->maximumFractionDigits > 0 )
		{
			$dp .= "{" . $this->minimumFractionDigits . "," . $this->maximumFractionDigits . "}";
		}
		else
		{
			$dp .= "{" . $this->minimumFractionDigits . ",}";
		}
	
		$numberPattern .= "($dp)?";
		
		return "$numberPattern";
	}
	
	private function regenerateRegularExpression()
	{
		$this->numberRegularExpression = $this->generateRegularExpression();
	}

	//  ------------------------------------->

	public function format($number)
	{
		$neg	    = $number < 0;
		$number	    = abs($number * $this->multiplier);
		$integer    = (int)$number;
		$decimalsS  = substr("$number", strlen("$integer") + ($integer < $number ? 1 : 0));
		$decimalsL  = strlen($decimalsS);
		$decimals   = floatval("0.$decimalsS");
		$numberS    = "";

		/*
		 On gère la partie décimale
		*/

		$d = $decimalsS;

		if ( $this->maximumFractionDigits > -1 && $decimalsL > $this->maximumFractionDigits )
		{
			$p = pow(10, $this->maximumFractionDigits);
			$d = (int)round($decimals * pow(10, $this->maximumFractionDigits));

			if ( $d >= $p )
			{
				$integer += (int)$d/$p;
				$d -= $p;
			}

			if ( $this->maximumFractionDigits == 0 || $d == 0 )
			{
				$decimalsS = "";
			}
			else
			{
				$decimalsS = $this->zeroPaddingNumber("$d", $this->maximumFractionDigits);
			}

			$decimalsL = strlen($decimalsS);
		}

		// minimum integer digits
		// on le passe après car le maximum peut faire des arrondis
		$decimalsS = $this->zeroPaddingNumber($decimalsS,  $this->minimumFractionDigits, false);
		$decimalsL = strlen($decimalsS);

		// point
		if ( $this->decimalSeparatorAlwaysShown || $decimalsL > 0 )
		{
			$numberS .= $this->symbols->getDecimalSeparator();
		}

		$numberS .= $decimalsS;

		/*
		 On gère la partie entière
		*/

		// minimum integer digits
		$integerS = $this->zeroPaddingNumber("$integer",  $this->minimumIntegerDigits);
		$integerL = strlen($integerS);

		// maximum integer digits
		if ( $this->maximumIntegerDigits > -1 && $integerL > $this->maximumIntegerDigits )
		{
			$integerS = substr($integerS, -$this->maximumIntegerDigits);
		}

		// grouping
		if ( $this->groupingUsed && $integerL > $this->groupingSize )
		{
			$gs = $this->symbols->getGroupingSeparator();
			$s  = "";
			for ( $j = 0, $i = $integerL - 1 ; $i >= 0 ; $i-- )
			{
				$s = $integerS[$i] . $s;

				if ( ++$j == $this->groupingSize && $i > 0 )
				{
					$s = "$gs$s";
					$j = 0;
				}
			}

			$integerS = $s;
		}

		$numberS = $integerS . $numberS;

		/*
		 Maintenant on format le nombre
		*/

		$compiledPattern = null;
		if ( $neg )
		{
			if ( empty($this->compiledNegativePattern) )
			{
				$numberS = "-$numberS";
				$compiledPattern = $this->compiledPositivePattern;
			}
			else
			{
				$compiledPattern = $this->compiledNegativePattern;
			}
		}
		else
		{
			$compiledPattern = $this->compiledPositivePattern;
		}

		$symbols = $this->getSymbolsArray();
		
		$format = "";
		$l = sizeof($compiledPattern);
		for ( $i = 0 ; $i < $l ;  )
		{
			$s = $compiledPattern[$i++];
			$v = $compiledPattern[$i++];

			switch ( $s )
			{
				case self::STR : $format .= $v; break;
				case self::SPE : $format .= $symbols[$v]; break;
				case self::NUM : $format .= $numberS; break;
			}
		}

		return $format;
	}
	
	private function getSymbolsArray()
	{
		$symbols = Array();
		$symbols["c"] = $this->symbols->getCurrencySymbol();
		$symbols["p"] = $this->symbols->getPercent();
		$symbols["-"] = $this->symbols->getMinusSign();
		$symbols["E"] = $this->symbols->getExponential();
		$symbols["m"] = $this->symbols->getPermill();
		$symbols["i"] = $this->symbols->getInfinity();
		$symbols["%"] = "%";
		
		return $symbols;
	}

	private function zeroPaddingNumber($s, $nb, $before=true)
	{
		$l = $nb - strlen($s);
		if ( $before )
		{
			for ( $i = 0 ; $i < $l ; $i++ )
			{
				$s = "0$s";
			}
		}
		else
		{
			for ( $i = 0 ; $i < $l ; $i++ )
			{
				$s = $s . "0";
			}
		}

		return $s;
	}
	
	//  ------------------------------------->
	
	public function parse($s)
	{
		/*
		 * Calcul de la regular expression entière
		 */
		
		$posreg = $this->generateFullExpressionFor($this->compiledPositivePattern);
		$negreg = $this->generateFullExpressionFor($this->compiledNegativePattern);
		
		/*
		 * Parcours la chaine et remplace les séparateurs des milliers et des décimales
		 * pour être compatible avec la regexp
		 */
		
		$l = mb_strlen($s);
		
		$isNumber = false;
		$isDecimal = false;
		$lastChar = null;
		$c = null; // char courrant
		 
		$t = ""; // chaîne transformée
		for ( $i = 0 ; $i < $l ; $i++ )
		{
			$lastChar = $c;
			$c = mb_substr($s, $i, 1);
			$o = ord($c);
			$n = ($o >= 48 && $o <= 57);
			$nd = ($n || $c == $this->symbols->getDecimalSeparator());
			
			if ( !$isNumber )
			{
				if ( $nd )
				{
					$isNumber = true;
				}
				else 
				{
					// on ne remplace que si on est dans un nombre
					$t .= $c;
					continue;
				}
			}
			
			// on est dans la partie décimale et ce n'est pas un nombre
			if ( $isDecimal && !$n )
			{
				break;
			}
			
			if ( $lastChar == $this->symbols->getGroupingSeparator() )
			{
				if ( $nd )
				{
					$t .= ',';
				}
				else
				{
					$t .= $this->symbols->getGroupingSeparator();
				}
			}
			
			if ( $c == $this->symbols->getGroupingSeparator() )
			{
				// on passe, on le gèrera la prochaine fois
				// $t .= ",";
				continue;
			}
			else if ( $c == $this->symbols->getDecimalSeparator() )
			{
				$t .= '.';
				$isDecimal = true;
			}
			else if ( $n )
			{
				$t .= $c;
			}
			else
			{
				// ni un catactères ni un séparateur
				break;
			}
		}
		
		// on ajoute les caractères manquants
		for ( ; $i < $l ; $i++ )
		{
			$c = mb_substr($s, $i, 1);
			$t .= $c;
		}
		
		if ( ($p = mb_ereg($posreg, $t, $matches)) < 1 && ($n = mb_ereg($negreg, $t, $matches)) < 1 )
		{
			throw new Exception("Can't parse number : pattern do not match");
		}
		
		$s = $matches[1];
		$l = mb_strlen($s);
		$number = "";
		for ( $i = 0 ; $i < $l ; $i++ )
		{
			$c = mb_substr($s, $i, 1);
			
			if ( $c == "," )
			{
				$number .= "";
			}
			else 
			{
				$number .= $c;
			}
		}	
		
		return floatval($number);
	}
	
	private function generateFullExpressionFor($compiledPattern)
	{
		$symbols = $this->getSymbolsArray();
		
		$reg = "";
		
		$l = sizeof($compiledPattern);
		for ( $i = 0 ; $i < $l ;  )
		{
			$s = $compiledPattern[$i++];
			$v = $compiledPattern[$i++];
		
			switch ( $s )
			{
				case self::STR : $reg .= preg_quote($v); break;
				case self::SPE : $reg .= preg_quote($symbols[$v]); break;
				case self::NUM : $reg .= "($this->numberRegularExpression)"; break;
			}
		}
		
		return "^$reg$";
	}
	
	//  ------------------------------------->

	public function isDecimalSeparatorAlwaysShown()
	{
		return $this->decimalSeparatorAlwaysShown;
	}

	public function setDecimalSeparatorAlwaysShown($shown)
	{
		$this->decimalSeparatorAlwaysShown = ($shown == true);
		$this->regenerateRegularExpression();
	}

	public function isGroupingUsed()
	{
		return $this->groupingUsed;
	}

	public function setGroupingUsed($used)
	{
		$this->groupingUsed = ($used == true);
		$this->regenerateRegularExpression();
	}

	public function getGroupingSize()
	{
		return $this->groupingSize;
	}

	public function setGroupingSize($size)
	{
		$this->groupingSize = ($size > 0 ? $size : 0);
		$this->groupingUsed = $this->groupingSize > 0;
		$this->regenerateRegularExpression();
	}

	public function getMaximumFractionDigits()
	{
		return $this->maximumFractionDigits;
	}

	public function setMaximumFractionDigits($max)
	{
		$this->maximumFractionDigits = ($max < 0 ? -1 : $max);
		if ( $this->maximumFractionDigits >= 0 && $this->maximumFractionDigits < $this->minimumFractionDigits )
		{
			$this->minimumFractionDigits = $max;
		}
		
		$this->regenerateRegularExpression();
	}

	public function getMinimumFractionDigits()
	{
		return $this->minimumFractionDigits;
	}

	public function setMinimumFractionDigits($min)
	{
		$this->minimumFractionDigits = ($min > 0 ? $min : 0);
		if ( $this->maximumFractionDigits < $this->minimumFractionDigits )
		{
			$this->maximumFractionDigits = $this->minimumFractionDigits;
		}
		
		$this->regenerateRegularExpression();
	}

	public function getMaximumIntegerDigits()
	{
		return $this->maximumIntegerDigits;
	}

	public function setMaximumIntegerDigits($max)
	{
		$this->maximumIntegerDigits = ($max < 0 ? -1 : $max);
		if ( $this->maximumIntegerDigits >= 0 && $this->maximumIntegerDigits < $this->minimumIntegerDigits )
		{
			$this->minimumIntegerDigits = $max;
		}
		
		$this->regenerateRegularExpression();
	}

	public function getMinimumIntegerDigits()
	{
		return $this->minimumIntegerDigits;
	}

	public function setMinimumIntegerDigits($min)
	{
		$this->minimumIntegerDigits = ($min > 0 ? $min : 0);
		if ( $this->maximumIntegerDigits < $this->minimumIntegerDigits )
		{
			$this->maximumIntegerDigits = $this->minimumIntegerDigits;
		}
		
		$this->regenerateRegularExpression();
	}

	public function getMultiplier()
	{
		return $this->multiplier;
	}

	public function setMultiplier($multiplier)
	{
		return $this->multiplier = $multiplier;
	}

	public function setCurrency(Currency $currency, $symbol=null)
	{
		$this->symbols->setCurrency($currency);
		if ( !empty($symbol) )
		{
			$this->setCurrencySymbol($symbol);
		}
	}

	public function setCurrencySymbol($symbol)
	{
		$this->symbols->setCurrencySymbol($symbol);
	}

	public function getCurrency()
	{
		return $this->symbols->getCurrency();
	}

	public function getCurrencySymbol()
	{
		return $this->symbols->getCurrencySymbol();
	}

	/**
	 * Retourne les symbols utilisés
	 * @return NumberFormatSymbols
	 */
	public function getSymbols()
	{
		return $this->symbols->duplicate();
	}

	public function setSymbols(NumberFormatSymbols $symbols)
	{
		$this->symbols = $symbols->duplicate();
	}

	//  ------------------------------------->

	public function toJSON()
	{
		$symbols = new JSONPropertyBag();
		$symbols->add('currency',	    $this->symbols->getCurrencySymbol());
		$symbols->add('groupingSeparator',  $this->symbols->getGroupingSeparator());
		$symbols->add('decimalSeparator',   $this->symbols->getDecimalSeparator());
		$symbols->add('percent',	    $this->symbols->getPercent());
		$symbols->add('minusSign',	    $this->symbols->getMinusSign());
		$symbols->add('exponential',	    $this->symbols->getExponential());
		$symbols->add('permill',	    $this->symbols->getPermill());
		$symbols->add('infinity',	    $this->symbols->getInfinity());

		$format = new JSONPropertyBag();
		$format->add('multiplier', $this->multiplier);
		$format->add('minimumFractionDigits', $this->minimumFractionDigits);
		$format->add('maximumFractionDigits', $this->maximumFractionDigits);
		$format->add('minimumIntegerDigits', $this->minimumIntegerDigits);
		$format->add('maximumIntegerDigits', $this->maximumIntegerDigits);
		$format->add('groupingUsed', $this->groupingUsed);
		$format->add('groupingSize', $this->groupingSize);
		$format->add('decimalSeparatorAlwaysShown', $this->decimalSeparatorAlwaysShown);
		$format->add('symbols', $symbols);

		// on crée maintenant les positive et negative pattern
		$format->add('positivePattern', $this->compiledPatternToJSON($this->compiledPositivePattern));
		$format->add('negativePattern', empty($this->compiledNegativePattern) ? null : $this->compiledPatternToJSON($this->compiledNegativePattern));

		return $format;
	}

	private function compiledPatternToJSON($compiledPattern)
	{
		$format = "";
		$l = sizeof($compiledPattern);
		for ( $i = 0 ; $i < $l ;  )
		{
			$s = $compiledPattern[$i++];
			$v = $compiledPattern[$i++];

			switch ( $s )
			{
				case self::STR : $format .= "'$v'"; break;
				case self::SPE : $format .= $v; break;
				case self::NUM : $format .= "#"; break;
			}
		}

		return "$format";
	}

	//  ------------------------------------->

	public static function getForPattern($pattern, $locale=null)
	{
		if ( empty($locale) )
		{
			$locale = Localisation::getCurrentLocale();
		}

		return new NumberFormat($pattern, $locale);
	}

	private static function getForStyle($style, $locale=null)
	{
		if ( empty($locale) )
		{
			$locale = Localisation::getCurrentLocale();
		}

		$i = ($style == self::INTEGER_STYLE ? self::DECIMAL_STYLE : $style);
		$p = null;

		$patterns = $locale->getProperties('NumberPatterns');
		if ( !empty($patterns) && sizeof($patterns) > $i )
		{
			$p = $patterns[$i];
		}

		if ( empty($p) )
		{
			throw new Exception('No number pattern found this style');
		}

		$format = self::getForPattern($p, $locale);
		if ( $style == self::INTEGER_STYLE )
		{
			$format->setMaximumFractionDigits(0);
		}

		return $format;
	}

	public static function getIntegerInstance($locale=null)
	{
		return self::getForStyle(self::INTEGER_STYLE, $locale);
	}

	public static function getDecimalInstance($locale=null)
	{
		return self::getForStyle(self::DECIMAL_STYLE, $locale);
	}

	public static function getCurrencyInstance($locale=null, $currency=null, $symbol=null)
	{
		$format = self::getForStyle(self::CURRENCY_STYLE, $locale);

		if ( !empty($currency) )
		{
			$format->setCurrency($currency, $symbol);
		}
		else if ( !empty($symbol) )
		{
			$format->setCurrencySymbol($symbol);
		}

		/*
		 On ajuste le nombre de fraction digits
		en fonction du nombre définit au niveau de la currency
		FIXME doit on ajuste en fonction de la currency par défaut pour la locale
		ou en fonction de la curency donnée
		*/

		$c = $format->getCurrency();
		if ( !empty($c) )
		{
			$n = $c->getDefaultFractionDigits();
			if ( $n >= 0 )
			{
				if ( $format->minimumFractionDigits == $format->maximumFractionDigits )
				{
					$format->setMinimumFractionDigits($n);
					$format->setMaximumFractionDigits($n);
				}
				else
				{
					$format->setMinimumFractionDigits(min($n, $format->minimumFractionDigits));
					$format->setMaximumFractionDigits($n);
				}
			}
		}

		return $format;
	}

	public static function getPercentInstance($locale=null)
	{
		return self::getForStyle(self::PERCENT_STYLE, $locale);
	}
}
