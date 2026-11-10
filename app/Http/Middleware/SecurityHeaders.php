<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tarayıcıya güvenlik başlıkları gönderir.
 * Sayfalar başka sitelerde çerçeve içine alınamaz (clickjacking), içerik türü tahmin edilmez,
 * başka sitelere giden isteklerde tam adres paylaşılmaz; kamera, mikrofon ve konum izni istenmez.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=()',
            // Satır içi script'lere dokunmadan çerçeveleme, eklenti ve <base> etiketi kısıtlanır
            'Content-Security-Policy' => "frame-ancestors 'self'; object-src 'none'; base-uri 'self'; form-action 'self'",
        ];

        // HTTPS üzerinden gelen isteklerde tarayıcı bir yıl boyunca yalnızca HTTPS kullansın
        if ($request->isSecure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        foreach ($headers as $name => $value) {
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }
}
