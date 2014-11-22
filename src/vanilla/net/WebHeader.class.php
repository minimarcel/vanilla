<?php

class WebHeader
{
    private $name;
    private $value;
    private $line;

// -------------------------------------------------------------------------->

    public function __construct($name, $value)
    {
	$this->name	= trim($name);
	$this->value	= trim($value);
	$this->line	= empty($value) ? null : "$name: $value";
    }

// -------------------------------------------------------------------------->

    public function getName()
    {
	return $this->name;
    }

    public function getValue()
    {
	return $this->value;
    }

    public function getLine()
    {
	return $this->line;
    }
}
?>
