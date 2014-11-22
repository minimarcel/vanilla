<?php

import('vanilla.gfx.GfxDriver');

import('vanilla.gfx.gd.GdImage');
import('vanilla.gfx.gd.GdImageInfo');

/**
 *
 */
class GdDriver implements GfxDriver
{
	private $defaultJPEGQuality = 85;

	public function getVersion()
	{
		return "0.1";
	}

	public function loadImageFromFile(File $file)
	{
		$info = self::getImageInfoFromFile($file);
		$path = $file->getPath();

		$resource = null;
		switch ( $info->getFormat() )
		{
			case ImageInfo::GIF	    : $resource = imagecreatefromgif($path); break;
			case ImageInfo::JPEG    : $resource = imagecreatefromjpeg($path); break;
			case ImageInfo::PNG	    : $resource = imagecreatefrompng($path); break;
		}

		if ( empty($resource) )
		{
			throw new GfxException("Unknwon Image type of : " . $info->getMimeType());
		}

		return new GdImage($resource);
	}

	public function getImageInfoFromFile(File $file)
	{
		return new GdImageInfo( getimagesize($file->getPath()) );
	}

	public function createImage($width, $height, /*Color */$background=null)
	{
		if ( $width <= 0 || $height <= 0 )
		{
			throw new GfxException("Invalid width, or height; must be strictly positive");
		}

		$res = imagecreatetruecolor($width, $height);
		if ( function_exists('imagesavealpha') )
		{
			imagesavealpha($res, true);
		}

		if ( empty($background) )
		{
			$background = Color::RGB(0xFF, 0xFF, 0xFF);
		}

		imagefill($res, 0, 0, GdGraphics::toGdColor($res, $background));

		return new GdImage($res);
	}

	public function writeImageToFile(Image $image, File $file, $format)
	{
		$exists = $file->exists();
		
		$path = $file->getPath();
		$res  = $image->getResource();

		//imagealphablending($res, false);

		switch ( $format )
		{
			case ImageInfo::GIF	    : imagegif($res, $path); break;
			case ImageInfo::JPEG    : imagejpeg($res, $path, $this->defaultJPEGQuality); break;
			case ImageInfo::PNG	    : imagepng($res, $path); break;
			default		    : throw new GfxException("Unknwon Image format : " . $format);
		}
		
		if ( !$exists && $file->exists() )
		{
			try
			{
				chmod($file->getPath(), File::$fileMode);
			}
			catch(Exception $e)
			{}
		}
	}

	public function writeImageToStandardOutput(Image $image, $format)
	{
		$res  = $image->getResource();

		switch ( $format )
		{
			case ImageInfo::GIF	    : imagegif($res); break;
			case ImageInfo::JPEG    : imagejpeg($res, null, $this->defaultJPEGQuality); break;
			case ImageInfo::PNG	    : imagepng($res); break;
			default		    : throw new GfxException("Unknwon Image format : " . $format);
		}
	}

	public function getDefaultJPEGQuality()
	{
		return $this->defaultJPEGQuality;
	}

	public function setDefaultJPEGQuality($quality)
	{
		$this->defaultJPEGQuality = $quality;
	}

	/*
	 * Renvoie le rectangle entourant une chaîne pour une font donnée
	*/
	public function getStringBoundingBox(Font $font, $str, $angle=0)
	{
		$size = $font->getSize();
		$file = $font->getFile()->getPath();

		// TODO gérer la valeur en point ou pixel suivant la
		// TODO gérer l'orientation
		// gd1 ou gd2
		$b = imagettfbbox($size, $angle, $file, $str);

		return new Rectangle($b[6], $b[7], abs($b[4] - $b[6]), abs($b[1] - $b[7]));
	}
}
?>
