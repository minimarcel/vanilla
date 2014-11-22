<?php

import('vanilla.text.plist.PListDictionary');

class PList
{
    private $dictionary;

//  -------------------------------------------------->

    public function __construct()
    {
	$this->dictionary = new PListDictionary();
    }

//  -------------------------------------------------->

    public function getDictionary()
    {
	return $this->dictionary;
    }

//  -------------------------------------------------->

    public function toXML()
    {
	$imp = new DOMImplementation();

	$docType = $imp->createDocumentType("plist", "-//Apple//DTD PLIST 1.0//EN", "http://www.apple.com/DTDs/PropertyList-1.0.dtd");
	$dom = $imp->createDocument(null, null, $docType);
	
	$node = $dom->createElement("plist");
	$node->setAttribute("version", "1.0");
	$node->appendChild($this->dictionary->serialize($dom));

	$dom->appendChild($node);

	return $dom;
    }
}
?>
