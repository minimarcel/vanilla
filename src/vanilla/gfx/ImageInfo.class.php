<?php

import('vanilla.gfx.Dimension');

/**
 * 
 */
interface ImageInfo
{
    const JPEG	= "JPEG";
    const GIF	= "GIF";
    const PNG	= "PNG";

//  ----------------------------------------------->

    public function getWidth();
    public function getHeight();
    public function getSize();
    public function getFormat();
    public function getMimeType();
    public function getBits();
}
?>
