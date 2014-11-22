<?php

import('vanilla.runtime.URLRewritingRule');
import('wisy.WisyURL');

import('vanilla.routing.RoutingTable');

/**
 * 
 */
class RoutingRewritingRule implements URLRewritingRule
{
    /**
     *
     */
    public function init(URLRewritingConfig $config)
    {}

    /**
     *
     */
    public function execute(URLRW $url)
    {
	RoutingTable::getInstance()->rewriteURL($url);
	return $url;
    }
}
?>
