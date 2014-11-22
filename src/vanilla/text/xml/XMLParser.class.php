<?php

import("vanilla.text.xml.XMLParserHandler");

class XMLParser
{
    private $parser;
    private $handler;

//  ------------------------------------->

    public function __construct(XMLParserHandler $handler) 
    {
	$this->parser	= xml_parser_create();
	$this->handler	= $handler;

	xml_set_object($this->parser, $this);
	xml_set_element_handler($this->parser, "startElement", "endElement");
	xml_set_character_data_handler($this->parser, "cdata");
    }

    public function __destruct()
    {
	if ( !empty($this->parser) )
	{
	    xml_parser_free($this->parser);	
	}
    }

//  ------------------------------------->

    public function getResource()
    {
	return $this->parser;
    }

    public function parse(TextReader $reader)
    {
	while ( ($b = $reader->read(1024)) !== false )
	{
	    xml_parse($this->parser, $b);
	}
    }

//  ------------------------------------->

    protected function startElement($parser, $name, $attributes)
    {
	$this->handler->startElement($this, $name, $attributes);
    }

    protected function endElement($parser, $name)
    {
	$this->handler->endElement($this, $name);
    }

    protected function cdata($parser, $data)
    {
	$this->handler->cdata($this, $data);
    }
}
?>
