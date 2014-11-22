<?php

interface XHTMLNode
{
    const TAG_TYPE	    = 'TAG';
    const TEXT_TYPE	    = 'TEXT';
    const COMMENT_TYPE	    = 'COMMENT';

//  ------------------------------------->

    public function getNodeName();
    public function getNodeType();
}
?>
