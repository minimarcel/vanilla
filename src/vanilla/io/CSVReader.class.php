<?php

import('vanilla.io.TextReader');
import('vanilla.util.ArrayList');

class CSVReader 
{
    const DEFAULT_STRING_SEPARATOR  = '"';
    const DEFAULT_FIELD_SEPARATOR   = ',';

// -------------------------------------------------------------------------->

    private $reader;
    private $stringSeparator;
    private $fieldSeparator;

// -------------------------------------------------------------------------->

    public function __construct(TextReader $reader)
    {
	$this->reader	= $reader;
	$this->stringSeparator	= self::DEFAULT_STRING_SEPARATOR;
	$this->fieldSeparator	= self::DEFAULT_FIELD_SEPARATOR;
    }

// -------------------------------------------------------------------------->

    /**
     * Renvoie un tableau 
     */
    public function readFields() 
    {
	$line = $this->reader->readln();
	if ( $line === false )
	{
	    return null;
	}

	return $this->parseLine($line);
    }

    private function parseLine($line)
    {
        $array	    = new ArrayList();
        $isInString = false;
        $s	    = '';

	while ( $line !== false )
	{
	    $l = mb_strlen($line);
	    for ( $i = 0 ; $i < strlen($line) ; $i++ )
	    {
		$c = mb_substr($line, $i, 1);

		if ( $isInString  )
		{
		    if ( $c == $this->stringSeparator )
		    {
			$i++;
			if ( $i >= mb_strlen($line) || mb_substr($line, $i, 1) != $this->stringSeparator )
			{
			    $i--;
			    $isInString = false;
			    continue;
			}
		    }
		}
		else if ( $c == $this->stringSeparator )
		{
		    $isInString = true;
		    continue;
		}
		else if ( $c == $this->fieldSeparator )
		{
		    $array->add($s);
		    $s = '';
		    continue;
		}

		$s .= $c;
	    }

	    if ( !$isInString )
	    {
		// on a atteint la fin
		$array->add($s);
		break;
	    }
	    else
	    {
		// la chaine est ouverte, on continue sur la ligne suivante
		$line = $this->reader->readln();
	    }
	}

        return $array->elements;
    }

// -------------------------------------------------------------------------->

    public function setStringSeparator($separator)
    {
	$this->stringSeparator = $separator;
    }

    public function setFieldSeparator($separator)
    {
	$this->fieldSeparator = $separator;
    }
}
?>
