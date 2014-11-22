<?php

import('vanilla.io.Base64EncoderWriter');

class MimeBEncoder extends Base64EncoderWriter
{
    public function __construct(Writer $writer)
    {
	parent::__construct($writer, -1);
    }

// -------------------------------------------------------------------------->

    public static function encodedLength($s)
    {
	return (int)(((strlen($s) + 2) / 3) * 4);
    }
}
