<?php

class CSSProperty
{
    private /*String*/ $name;
    private /*String*/ $value;
    private /*bool*/ $important=false;

//  ------------------------------------->

    public function __construct($name, $value, $important=false)
    {
	$this->name = $name;
	$this->value = $value;
	$this->important = ($important == true);
    }

//  ------------------------------------->

    public function getName()
    {
	return $this->name;
    }

    public function getValue()
    {
	return $this->value;
    }

    public function setValue($value)
    {
	$this->value = $value;
    }

    public function isImportant()
    {
	return $this->important;
    }

//  ------------------------------------->

    public function __toString()
    {
	return "$this->name : $this->value" . ($this->important ? " !important" : "") . ";";
    }
}
?>
