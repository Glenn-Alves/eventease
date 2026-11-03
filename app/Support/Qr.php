<?php

namespace App\Support;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class Qr
{
    public static function svg(string $text, int $size = 260): string
    {
        $renderer = new ImageRenderer(new RendererStyle($size, 2), new SvgImageBackEnd());
        $svg = (new Writer($renderer))->writeString($text);

        // Remove the XML line so the SVG can sit inside an HTML page
        return preg_replace('/^<\?xml[^>]*\?>\s*/', '', $svg);
    }
}