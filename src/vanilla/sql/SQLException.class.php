<?php

/**
 * 
 */
class SQLException extends Exception
{
    private $query;
    
//------------------------------>
    
    public function __construct($message, $code = 0, $query = null)
    {
	parent::__construct($message, $code);
	$this->query = $query;
    } 

//------------------------------>
    
    public function __toString() 
    {
	$s = parent::__toString();

	if ( !empty($this->code) )
	{
	    $s = $s . "\nSQLCode : $this->code";
	}
	
	if ( !empty($this->query) )
	{
	    $s = $s . "\nQuery was : \n\t" . $this->query;
	}
	
	return $s;
    }
}
?>
