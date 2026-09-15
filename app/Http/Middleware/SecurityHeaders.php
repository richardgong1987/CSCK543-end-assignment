<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds the HTTP security headers from proposal §8.2 to every response.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // Every <script> and <style> tag that @vite and @fonts print carries this nonce, so the
        // policy can refuse any inline code that did not come from them.
        Vite::useCspNonce();

        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        // Only over HTTPS: over plain HTTP browsers ignore it, and on a local copy it would
        // be pointless.
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        if (! $this->isDebugErrorPage($response)) {
            $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy());
        }

        return $response;
    }

    private function contentSecurityPolicy(): string
    {
        $nonce = "'nonce-".Vite::cspNonce()."'";

        $directives = [
            'default-src' => ["'self'"],
            'script-src' => ["'self'", $nonce],
            'style-src' => ["'self'", $nonce],
            'img-src' => ["'self'", 'data:'],
            'font-src' => ["'self'"],
            'connect-src' => ["'self'"],
            'form-action' => ["'self'"],
            'frame-ancestors' => ["'none'"],
            'base-uri' => ["'self'"],
            'object-src' => ["'none'"],
        ];

        if (Vite::isRunningHot()) {
            // Development only. Scripts, fonts and the live-reload socket come from the Vite dev
            // server, which also injects CSS as <style> tags without a nonce; a nonce in a
            // directive switches 'unsafe-inline' off, so style-src drops it here.
            //
            // The dev server is allowed by scheme rather than by its address, because Vite often
            // listens on an IPv6 address such as http://[::1]:5173, and a policy cannot name
            // one: browsers ignore the entry and the page loads unstyled.
            $directives['script-src'][] = 'http:';
            $directives['style-src'] = ["'self'", "'unsafe-inline'", 'http:'];
            $directives['font-src'][] = 'http:';
            array_push($directives['connect-src'], 'http:', 'ws:');
        }

        return collect($directives)
            ->map(fn (array $sources, string $directive) => $directive.' '.implode(' ', $sources))
            ->implode('; ');
    }

    /**
     * Laravel's debug error page is built from inline scripts and styles, so a strict policy
     * would show a developer an unstyled page instead of the error.
     */
    private function isDebugErrorPage(Response $response): bool
    {
        return config('app.debug') && $response->getStatusCode() >= 500;
    }
}
