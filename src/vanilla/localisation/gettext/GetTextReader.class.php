<?php
import('vanilla.io.SeekReader');

/**
 * FIXME charset ??
 */
class GetTextReader 
{
    /**
     * The MAGIC const for little endia bit order 
     */
    const MAGIC_LITTLE_ENDIAN	= 0x950412DE;

    /**
     * The MAGIC const for little endia bit order 
     */
    const MAGIC_BIG_ENDIAN	= 0xDE120495;

// -------------------------------------------------------------------------->

    private $reader;
    private $littleEndian = true;

    private $revision;
    private $nbStrings;

    private $originalTable;
    private $translationTable;

// -------------------------------------------------------------------------->

    public function __construct(SeekReader $reader)
    {
	$this->reader = $reader;

	/*
	   On vérifie le magic number
	*/

	$magic = $this->readInt();

	//if ( $magic == (int)self::MAGIC_LITTLE_ENDIAN || $magic == (int)2500072158 )
	if ( $magic == (int)-1794895138 || $magic == (int)2500072158 )
	{
	    // FIXME et l'hexa ça marche comment ?
	    $this->littleEndian = true;
	}
	else if (  $magic == ((int)self::MAGIC_BIG_ENDIAN & 0xFFFFFFFF) )
	{
	    $this->littleEndian = false;
	}
	else
	{
	    throw new IOException('Invalid gettext file');
	}

	/*
	   On les informations du header
	*/

	$this->revision			= $this->readInt();
	$this->nbStrings		= $this->readInt();
	$originalTableOffset		= $this->readInt();
	$translationTableOffset		= $this->readInt();

	/*
	   Lecture des tables
	*/

	$this->originalTable		= $this->readTable($originalTableOffset);
	$this->translationTable		= $this->readTable($translationTableOffset);
    }

// -------------------------------------------------------------------------->

    private function readInt()
    {
	if ( $this->littleEndian )
	{
	    $a = unpack('V', $this->reader->read(4));
	}
	else
	{
	    $a = unpack('N', $this->reader->read(4));
	}

	return array_shift($a);
    }

    private function &readTable($offset)
    {
	$this->reader->seekTo($offset);

	$n = $this->nbStrings * 2;
	if ( $this->littleEndian )
	{
	    $a = unpack("V$n", $this->reader->read(4 * $n));
	}
	else
	{
	    $a = unpack("N$n", $this->reader->read(4 * $n));
	}
	
	return $a;
    }

// -------------------------------------------------------------------------->

    public function getRevision()
    {
	return $this->revision;
    }

    public function getNbStrings()
    {
	return $this->nbStrings;
    }

// -------------------------------------------------------------------------->

    public function getOriginalString($index)
    {
	return $this->getString($this->originalTable, $index);
    }

    public function getTranslationString($index)
    {
	return $this->getString($this->translationTable, $index);
    }

    private function getString(&$table, $index)
    {
	$index *= 2;
	$l = $table[$index + 1];
	$i = $table[$index + 2];

	$this->reader->seekTo($i);
	return $this->reader->read($l);
    }
}
?>
