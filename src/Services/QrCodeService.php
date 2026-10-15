<?php

namespace TwoFA\LaravelMfa\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeService
{
    public function dataUri(string $contents): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle((int) config('mfa.qr.size', 220), (int) config('mfa.qr.margin', 1)),
            new SvgImageBackEnd()
        );

        $svg = (new Writer($renderer))->writeString($contents);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
