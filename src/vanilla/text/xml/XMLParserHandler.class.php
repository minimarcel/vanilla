<?php

import("vanilla.text.xml.XMLParser");

interface XMLParserHandler
{
    public function startElement(XMLParser $parser, $name, $attributes);
    public function endElement(XMLParser $parser, $name);
    public function cdata(XMLParser $parser, $data);
}
?>
