<?php

namespace PHPUtils;

/**
 * Images
 * 
 * This class provides utility functions for working with images using the Imagick extension.
 * 
 * @package PHPUtils
 * @author Darknetzz
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 * 
 * @example
 * $image = new Images();
 * $image->resize('/path/to/image.jpg', 200, 200);
 * 
 * @see https://www.php.net/manual/en/book.imagick.php
 */
class Images extends Base {
    /**
     * Images constructor.
     * 
     * Initializes the Images class and checks if the Imagick extension is enabled.
     * If the extension is not enabled, it terminates the script execution.
     */
    public function __construct(?Debugger $debugger = null, ?Vars $vars = null, bool $verbose = true) {
        parent::__construct($debugger, $vars, $verbose);
        if (!extension_loaded('imagick')) {
            throw new \RuntimeException("Imagick extension is not enabled. To use the class Images you must have the Imagick extension enabled.");
        }
    }

    /**
     * Blur an image.
     * 
     * @param string $imagePath The path to the image file.
     * @param float $radius The blur radius.
     * @param float $sigma The blur sigma.
     * @param int $channel The channel(s) to apply the blur to.
     * @return string The path of the blurred image.
     * @throws ImagickException If an error occurs while blurring the image.
     */
    public function blur(string $imagePath, float $radius = 10, float $sigma = 25, int $channel = imagick::CHANNEL_ALL) {
        try {
            // Create a temporary file
            $tmpDir          = dirname($imagePath);
            $tmpFile         = "blurred_".basename($imagePath);
            $tmpFullPath     = $tmpDir."/".$tmpFile;
            $tmpRelativePath = basename($tmpDir)."/".$tmpFile;

            if (is_file($tmpFullPath)) {
                return $tmpRelativePath;
            }

            $image = new Imagick($imagePath);
            $image->blurImage($radius, $sigma, $channel);
            $image->writeImage($tmpFullPath);

            // Schedule the file deletion when the script execution is finished
            register_shutdown_function(function() use ($tmpFullPath, $image) {
                $image->clear();
                $image->destroy();
                unlink($tmpFullPath);
            });

            return $tmpRelativePath; // return the path of the temporary file
        } catch (\ImagickException $e) {
            throw new \RuntimeException("Error blurring image: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Add text to an image.
     * 
     * @param string $text The text to add to the image.
     * @param string $imagePath The path to the image file.
     * @param string $fontPath The path to the font file.
     * @param int $fontSize The font size.
     * @param int $x The x-coordinate of the text position.
     * @param int $y The y-coordinate of the text position.
     * @param string $color The color of the text.
     * @throws ImagickException If an error occurs while adding the text to the image.
     */
    public function textToImage(string $text, string $imagePath, string $fontPath, int $fontSize, int $x, int $y, string $color = 'black') {
        $draw = new ImagickDraw();
        $draw->setFont($fontPath);
        $draw->setFontSize($fontSize);
        $draw->setFillColor($color);
        $draw->setGravity(Imagick::GRAVITY_NORTHWEST);
        $draw->annotation($x, $y, $text);

        $image = new Imagick($imagePath);
        $image->drawImage($draw);
        $image->writeImage($imagePath);
        $image->clear();
        $image->destroy();
    }

    /**
     * Render an image.
     * 
     * @param string $imagePath The path to the image file.
     * @param int $height The height of the image.
     * @param int $width The width of the image.
     * @return string The HTML <img> snippet.
     */
    public function renderImage(string $imagePath, int $height = 200, int $width = 200) {
        return '<img src="'.$imagePath.'" height="'.$height.'" width="'.$width.'">';
    }
}