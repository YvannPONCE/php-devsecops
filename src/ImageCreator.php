<?php

namespace App;

use Carbon\Carbon;

use function dirname;
use function imagecolorallocate;
use function imagecreatetruecolor;
use function imagedestroy;
use function imagefilledrectangle;
use function imagepng;
use function imagettftext;

/**
 * Class ImageCreator
 *
 * This class is responsible for creating an image with a custom design and text.
 */
class ImageCreator
{
    /**
     * The image resource identifier.
     *
     * @var resource
     */
    protected $im;

    /**
     * Color identifier for white.
     *
     * @var int
     */
    protected int $white;

    /**
     * Color identifier for the first custom color.
     *
     * @var int
     */
    protected int $yourColor;

    /**
     * Color identifier for the second custom color.
     *
     * @var int
     */
    protected int $yourColor2;

    /**
     * The main text to display on the image.
     *
     * @var string
     */
    protected string $text;

    /**
     * The subtitle text to display on the image.
     *
     * @var string
     */
    protected string $text2;

    /**
     * The font file path used for text rendering.
     *
     * @var string
     */
    protected string $font;

    /**
     * Constructor for the ImageCreator class.
     *
     * @param array $yourColor RGB values for the first custom color.
     * @param array $yourColor2 RGB values for the second custom color.
     * @param string $text The main text to display on the image.
     * @param string $text2 The subtitle text to display on the image.
     */
    public function __construct(
        array  $yourColor = [128, 128, 128],
        array  $yourColor2 = [60, 80, 57],
        string $text = "DEVOPS",
        string $text2 = "Une superbe image"
    ) {
        // Create a 600x200 pixel image
        $this->im = imagecreatetruecolor(600, 200);
        $this->white = $this->allocateColor([255, 255, 255]);
        $this->yourColor = $this->allocateColor($yourColor);
        $this->yourColor2 = $this->allocateColor($yourColor2);

        // Set the text
        $this->text = $text . ' - ' . (new Carbon())->format('Y-m-d H:i:s');
        $this->text2 = $text2;

        if (!empty($_ENV['APP_SECRET'])) {
            $this->text2 .= ' (secret: ' . $_ENV['APP_SECRET'] . ')';
        }

        // Set the font path
        $this->font = dirname(__DIR__) . '/public/font/consolas.ttf';
    }

    /**
     * Allocates a color for the image.
     *
     * @param array $rgb RGB values for the color.
     * @return false|int The color identifier or false on failure.
     */
    private function allocateColor(array $rgb): false|int
    {
        return imagecolorallocate($this->im, ...$rgb);
    }

    /**
     * Creates and renders the image with text and design.
     *
     * @return void
     */
    public function createImage(): void
    {
        // Draw a double rectangle
        imagefilledrectangle($this->im, 0, 0, 600, 200, $this->yourColor);
        imagefilledrectangle($this->im, 10, 10, 590, 190, $this->yourColor2);

        // Add text
        imagettftext($this->im, 20, 0, 50, 50, $this->white, $this->font, $this->text);
        imagettftext($this->im, 12, 0, 50, 80, $this->white, $this->font, $this->text2);

        // Save the image
        imagepng($this->im);
        imagedestroy($this->im);
    }
}
