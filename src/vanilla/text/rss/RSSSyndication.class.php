<?php

import('vanilla.text.rss.RSSChannelQualifiedNodes');
import('vanilla.text.rss.RSSNamespace');
import('vanilla.text.rss.RSSXMLHelper');

/**
 *  
 */
class RSSSyndication implements RSSChannelQualifiedNodes
{
    const PREFIX    = 'sy';
    const CODE	    = 'syndication';

//  ------------------------------------->

    const HOURLY    = 'hourly';
    const DAYLI	    = 'daily';
    const WEEKLY    = 'weekly';
    const MONTHLY   = 'monthly';
    const YEARLY    = 'yearly';

//  ------------------------------------->

    private $namespace;

    private $updatePeriod;
    private $updateFrequency;
    private $updateBase;

//  ------------------------------------->

    public function __construct($updatePeriod, $updateFrequency)
    {
	$this->namespace = new RSSNamespace(self::PREFIX, "http://purl.org/rss/1.0/modules/syndication/");
	$this->updatePeriod = $updatePeriod;
	$this->updateFrequency = $updateFrequency;
    }

//  ------------------------------------->

    public function getCode()
    {
	return self::CODE;
    }

    public function getNamespace()
    {
	return $this->namespace;
    }

    public function setUpdateBase(Date $updateBase)
    {
	$this->updateBase = $updateBase;
    }

    public function getUpdateBase()
    {
	return $this->updateBase;
    }

    public function getUpdatePeriod()
    {
	return $this->updatePeriod;
    }

    public function getUpdateFrequency()
    {
	return $this->updateFrequency;
    }
    
//  ------------------------------------->

    public function serialize()
    {
	$prefix = $this->namespace->getPrefix();

	return "" .
	    RSSXMLHelper::serializeNode("$prefix:updatePeriod", $this->updatePeriod) .
	    RSSXMLHelper::serializeNode("$prefix:updateFrequency", $this->updateFrequency) .
	    RSSXMLHelper::serializeNode("$prefix:updateBase", $this->updateBase);
    }
}
?>
