<?php

import('vanilla.sql.Connection');

/**
 * 
 */
interface Driver
{
    /**
     * Returns a new connection
     */
    public function connect($host, $database, $user, $pwd, $permanent=false);
    
    /**
     * Returns the driver version
     */
    public function getVersion();
}

?>
