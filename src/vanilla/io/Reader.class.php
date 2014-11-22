<?php

interface Reader
{
    /**
     * Retourne la chaîne (byte) lue, false si aucun byte n'est lu.
     */
    public function read($length);
    public function readToEnd();
    public function close();
}
?>
