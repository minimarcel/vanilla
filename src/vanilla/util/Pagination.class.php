<?php

import('vanilla.text.json.VJSONSerializable');

/**
 * 
 */
class Pagination implements VJSONSerializable
{
    private $page;	// la page courrante
    private $nbPerPage; // le nombre d'élements par page
    private $nbPages;	// le nombre de pages
    private $nbRecords; // le nombre de records par page

//  ---------------------------------------->

    public function __construct($page=1, $nbPerPage=10)
    {
	$this->page	    = ($page < 1 ? 1 : $page);
	$this->nbPerPage    = (empty($nbPerPage) ? 10 : $nbPerPage);
    }

//  ---------------------------------------->

    public function getPage()
    {
	return $this->page;
    }

    public function getNbPerPage()
    {
	return $this->nbPerPage;
    }

    public function setNbRecords($nbRecords)
    {
	$this->nbRecords    = ($nbRecords < 0 ? 0 : $nbRecords); 
	$this->nbPages	    = $this->nbPerPage <= 0 ? 1 : (int)ceil($this->nbRecords / $this->nbPerPage);

	if ( $this->nbPages <= 0 )
	{
	    $this->page = 0;
	}
	else if ( $this->page > $this->nbPages )
	{
	    $this->page = $this->nbPages;
	}
	else if ( $this->page <= 0 )
	{
	    $this->page = 1;
	}
    }

    public function getNbPages()
    {
	return $this->nbPages;
    }

    public function getNbRecords()
    {
	return $this->nbRecords;
    }

    public function getStartRecord()
    {
	return ($this->page - 1) * $this->nbPerPage;
    }

    public function isFirstPage()
    {
	return ($this->page == 1);
    }

    public function isLastPage()
    {
	return ($this->page == $this->nbPages);
    }

    public function isSinglePage()
    {
	return (($this->page == 1) && ($this->page == $this->nbPages));
    }

//  ---------------------------------------->

    public function toJSON()
    {
	$bag = new JSONPropertyBag();

	$bag->add("page",       $this->page);
	$bag->add("nbPerPage",  $this->nbPerPage);
	$bag->add("nbPages",    $this->nbPages);
	$bag->add("nbRecords",  $this->nbRecords); 

	return $bag;
    }
}
?>
