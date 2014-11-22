<?php

import('vanilla.runtime.URLRewritingConfig');

/**
 * 
 */
interface URLRewritingRule
{
    public function init(URLRewritingConfig $config);
    public function execute(URLRW $url);
}
?>
