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
 * Cette classe est responsable de la création d'une image avec du texte dynamique.
 * Elle utilise la librairie GD pour générer l'image et y dessiner du texte.
 * 
 * @package App
 */
class ImageCreator
{
    /**
     * @var resource L'objet image GD créé
     */
    protected $im;

    /**
     * @var int Couleur blanche pour l'image
     */
    protected int $white;

    /**
     * @var int Couleur principale utilisée pour l'image
     */
    protected int $yourColor;

    /**
     * @var int Deuxième couleur utilisée pour l'image
     */
    protected int $yourColor2;

    /**
     * @var string Le texte principal à afficher sur l'image
     */
    protected string $text;

    /**
     * @var string Le texte secondaire à afficher sur l'image
     */
    protected string $text2;

    /**
     * @var string Le chemin de la police de caractères
     */
    protected string $font;

    /**
     * ImageCreator constructor.
     *
     * @param array $yourColor La couleur principale
     * @param array $yourColor2 La deuxième couleur
     * @param string $text Le texte principal
     * @param string $text2 Le texte secondaire
     */
    public function __construct(
        array  $yourColor = [128, 128, 128],
        array  $yourColor2 = [60, 80, 57],
        string $text = "DEVOPS",
        string $text2 = "Une superbe image"
    ) {
        // Création d'une image de 400x200 pixels
        $this->im = imagecreatetruecolor(600, 200);
        $this->white = $this->allocateColor([255, 255, 255]);
        $this->yourColor = $this->allocateColor($yourColor);
        $this->yourColor2 = $this->allocateColor($yourColor2);

        // Le texte
        $this->text = $text . ' - ' . (new Carbon())->format('Y-m-d H:i:s');
        $this->text2 = $text2;

        if (!empty($_ENV['APP_SECRET'])) {
            $this->text2 .= ' (secret: ' . $_ENV['APP_SECRET'] . ')';
        }

        // La police
        $this->font = dirname(__DIR__) . '/public/font/consolas.ttf';
    }

    /**
     * Alloue une couleur à l'image
     *
     * @param array $rgb Le tableau de couleur (R, G, B)
     * @return false|int Retourne la couleur allouée, ou false si erreur
     */
    private function allocateColor(array $rgb): false|int
    {
        return imagecolorallocate($this->im, ...$rgb);
    }

    /**
     * Crée l'image et y ajoute les éléments graphiques
     *
     * Cette méthode dessine un rectangle sur l'image et y ajoute deux
     * textes personnalisés avant de sauvegarder l'image.
     *
     * @return void
     */
    public function createImage(): void
    {
        // Dessine un double rectangle
        imagefilledrectangle($this->im, 0, 0, 600, 200, $this->yourColor);
        imagefilledrectangle($this->im, 10, 10, 590, 190, $this->yourColor2);

        // Ajout du texte
        imagettftext($this->im, 20, 0, 50, 50, $this->white, $this->font, $this->text);
        imagettftext($this->im, 12, 0, 50, 80, $this->white, $this->font, $this->text2);

        // Sauvegarde l'image
        imagepng($this->im);
        imagedestroy($this->im);
    }

    // Aucune documentation pour cette méthode, elle devrait provoquer une erreur avec php-doc-check
    private function helperFunction()
    {
        // Code sans docblock
        return "Hello, world!";
    }
}
