<?php

interface PListProperty 
{
    /*
     * Return the plist property type
     */
    public function getType();

    /*
     * Return a DOMNode
     */
    public function serialize(DOMDocument $dom);
}
