<?php

import('vanilla.runtime.FilterConfig');

/**
 * 
 */
interface Filter
{
    public function init(FilterConfig $config);
    public function execute($resourceName);
}
?>
