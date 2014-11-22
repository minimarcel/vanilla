<?php

import('vanilla.util.Pagination');
import('vanilla.text.json.VJSONSerializable');

/**
 * 
 */
class PaginedList implements VJSONSerializable
{
    private $pagination;
    private $list;

//  ---------------------------------------->

    public function __construct(Pagination $pagination)
    {
	$this->pagination = $pagination;
    }

//  ---------------------------------------->

    public function getPagination()
    {
	return $this->pagination;
    }

    public function getList()
    {
	return $this->list;
    }

    public function setList(ArrayList $list)
    {
	$this->list = $list;
    }

    public function isEmpty()
    {
	return (empty($this->list) || $this->list->isEmpty());
    }

//  ---------------------------------------->

    public function toJSON()
    {
	$bag = new JSONPropertyBag();

	$bag->add("pagination", $this->pagination);
	$bag->add("list",	$this->list);

	return $bag;
    }
}
?>
