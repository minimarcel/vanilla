<?php
import('vanilla.io.Reader');

interface SeekReader extends Reader
{
    public function seekTo($position);
    public function getSize();
    public function getCurrentPosition();
}
?>
