<?php

import('vanilla.mail.MimePart');
import('vanilla.mail.MimeMultiPart');

class MimeBodyPart extends MimePart
{
    private $parent;

// -------------------------------------------------------------------------->

    public function __construct()
    {
	parent::__construct();
    }

// -------------------------------------------------------------------------->

    public function updateHeaders()
    {
	parent::updateHeaders();
    }

// -------------------------------------------------------------------------->

    public function getParent()
    {
	return $this->parent;
    }

    public function setParent(MimeMultiPart $multi)
    {
	$this->parent = $multi;
    }
}
?>
