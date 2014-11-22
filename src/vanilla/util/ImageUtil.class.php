<?php

import('vanilla.gfx.Gfx');
import('vanilla.gfx.Rectangle');
import('vanilla.io.File');

/**
 * Crop and resize images.
 * @see vanilla.gfx package
 */
class ImageUtil
{
    /**
     * Auto crop this image with the given fixed size
     */
    public static function autoCropForThumb(Image $image, Dimension $fixedSize)
    {
        $w = $image->getWidth();
        $h = $image->getHeight();

        $fw = $fixedSize->getWidth();
        $fh = $fixedSize->getHeight();

        /*
           We try to select the max surface of the image
        */

        $cropW = $w;
        $cropH = $cropW * $fh / $fw;

        if ( $cropH > $h )
        {
            $cropH = $h;
            $cropW = $cropH * $fw / $fh;
        }

        /*
           Reduce it to 66%
         */

        $size = $cropW / 1.5;
        if ( $size > $fw )
        {
            $cropW /= 1.5;
            $cropH = $cropW * $fh / $fw;
        }

        $cropW = intval($cropW);
        $cropH = intval($cropH);

        /*
           Center the zone
        */

        $cropX = intVal($w/2-$cropW/2);
        $cropY = intVal($h/2-$cropH/2);

        return $image->getScaledInstance($fw, $fh, $cropW, $cropH, $cropX, $cropY);
    }

    /**
     * Resize the given image in order to respect the two given constraints : 
     * - the width must not exceed the given maxWidth
     * - the height must not exceed the given maxHeight
     *
     * @see #cropAndResizeForMaxDimensions(vanilla.gfx.Image, vanilla.gfx.Rectangle, int, int)
     */
    public static function resizeForMaxDimensions(Image $image, $maxWidth=-1, $maxHeight=-1)
    {
        $source = new Rectangle();
        $source->setSize($image->getSize());

        return self::cropAndResizeForMaxDimensions($image, $source, $maxWidth, $maxHeight);
    }

    /**
     * Crop and resize the given image, by selecting a zone into the image defined by the source parameter, 
     * by respecting the two size (maxWidth and maxHeight) constraints, and scale it into the destination dimensions.
     *
     * The ratio of the source image is respected, and the destination image will respected the following constraints :
     * - the width must not exceed the given maxWidth
     * - the height must not exceed the given maxHeight
     */
    public static function cropAndResizeForMaxDimensions(Image $image, Rectangle $source, $maxWidth=-1, $maxHeight=-1, /*Dimention*/ $dest=null)
    {
        $width    = empty($dest) ? $source->getWidth() : $dest->getWidth();
        $height    = empty($dest) ? $source->getHeight() : $dest->getHeight();

        if ( $maxWidth > 0 && $width > $maxWidth )
        {
            $height *= $maxWidth/$width;
            $width = $maxWidth;
        }

        if ( $maxHeight > 0 && $height > $maxHeight )
        {
            $width *= $maxHeight/$height;
            $height = $maxHeight;
        }

        return $image->getScaledInstance($width, $height, $source->getWidth(), $source->getHeight(), $source->getX(), $source->getY());
    }

    /**
     * Resize the given image for the given fixed size.
     * @see #cropAndResizeForFixedSize(vanilla.gfx.Image, vanill.gfx.Rectangle, vanilla.gfx.Dimension)
     */
    public static function resizeForFixedSize(Image $image, Dimension $fixedSize)
    {
        $source = new Rectangle();
        $source->setSize($image->getSize());

        return self::cropAndResizeForFixedSize($image, $source, $fixedSize);
    }

    /**
     * Crop and resize the given image, by selecting a zone into the image defined by the source parameter,
     * and by returning an image with a size exactly equals to the given fixedSize 
     * (unlike the #cropAndResizeForMaxDimensions methods that will produce an image that can be represented in the given constraints)
     *
     * Note that ratio of the original image is respected but the image can be enlarged.
     */
    public static function cropAndResizeForFixedSize(Image $image, Rectangle $source, Dimension $fixedSize)
    {
        // on redéfinie le crop de manière à être sûr qu'il soit à la bonne taille
        $width  = $source->getWidth();
        $height = $width * $fixedSize->getHeight() / $fixedSize->getWidth();

        if ( $height > $source->getHeight() )
        {
            $height     = $source->getHeight();
            $width     = $height * $fixedSize->getWidth() / $fixedSize->getHeight();
        }

        return $image->getScaledInstance($fixedSize->getWidth(), $fixedSize->getHeight(), $width, $height, $source->getX(), $source->getY());
    }


    /**
     * Auto crop the image contained into the given file, and write it into the destination file.
     *
     * @see #autoCropForThumb(vanilla.gfx.Image, vanilla.gfx.Dimension)
     * @see vanilla.gfx.Gfx#writeImageToFile(vanilla.gfx.Image, vanilla.io.File, string)
     */
    public static function autoCropFileForThumb(File $file, Dimension $fixedSize, File $destFile=null)
    {
        $imageInfo  = Gfx::getImageInfoFromFile($file);
        $format     = $imageInfo->getFormat();
        $image      = Gfx::loadImageFromFile($file);
        $resized    = self::autoCropForThumb($image, $fixedSize);

        if ( empty($destFile) )
        {
            $destFile = $file;
        }

        Gfx::writeImageToFile($resized, $destFile, $format);

        $image->release();
        $resized->release();
    }

    /**
     * Resize the image contained into the given file with the given max constraints, 
     * and write it into the destination file.
     *
     * @see #resizeForMaxDimensions(vanilla.gfx.Image, int, int)
     * @see vanilla.gfx.Gfx#writeImageToFile(vanilla.gfx.Image, vanilla.io.File, string)
     */
    public static function resizeFileForMaxDimensions(File $file, $maxWidth=-1, $maxHeight=-1, File $destFile=null)
    {
        $imageInfo  = Gfx::getImageInfoFromFile($file);
        $format     = $imageInfo->getFormat();
        $image      = Gfx::loadImageFromFile($file);
        $resized    = self::resizeForMaxDimensions($image, $maxWidth, $maxHeight);

        if ( empty($destFile) )
        {
            $destFile = $file;
        }

        Gfx::writeImageToFile($resized, $destFile, $format);

        $image->release();
        $resized->release();
    }

    /**
     * Resize the image contained into the given file, and return an image that will have the given dimensions, 
     * and write it into the destination file.
     *
     * @see #resizeForFixedSize(vanilla.gfx.Image, vanilla.gfx.Dimension)
     * @see vanilla.gfx.Gfx#writeImageToFile(vanilla.gfx.Image, vanilla.io.File, string)
     */
    public static function resizeFileForFixedSize(File $file, Dimension $fixedSize, File $destFile=null)
    {
        $imageInfo  = Gfx::getImageInfoFromFile($file);
        $format     = $imageInfo->getFormat();
        $image      = Gfx::loadImageFromFile($file);
        $resized    = self::resizeForFixedSize($image, $fixedSize);

        if ( empty($destFile) )
        {
            $destFile = $file;
        }

        Gfx::writeImageToFile($resized, $destFile, $format);

        $image->release();
        $resized->release();
    }
}
?>
