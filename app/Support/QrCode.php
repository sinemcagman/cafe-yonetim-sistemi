<?php

namespace App\Support;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * Sayfaya gömülebilen SVG QR kod üretir (harici servis ya da görsel dosyası gerekmez).
 */
class QrCode
{
    /**
     * @param  int  $size  piksel cinsinden kenar uzunluğu (SVG olduğu için ölçeklenir)
     */
    public static function svg(string $content, int $size = 240): string
    {
        $renderer = new ImageRenderer(new RendererStyle($size, 1), new SvgImageBackEnd);

        // M seviyesi: kart biraz yıpransa da okunur, kod gereğinden yoğun olmaz
        $svg = (new Writer($renderer))->writeString($content, 'UTF-8', ErrorCorrectionLevel::M());

        // Satır içi kullanım için XML başlığını at, erişilebilirlik için rol ekle
        $svg = preg_replace('/^<\?xml[^>]*>\s*/', '', $svg);

        return preg_replace('/^<svg /', '<svg role="img" aria-label="QR kod" ', $svg);
    }
}
