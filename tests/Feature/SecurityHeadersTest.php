<?php

use Database\Seeders\RecipeSeeder;
use Database\Seeders\ReferenceDataSeeder;

beforeEach(function () {
    $this->seed([ReferenceDataSeeder::class, RecipeSeeder::class]);
});

it('sends the security headers with every kind of response', function (string $path) {
    $response = $this->get($path);

    $response->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

    expect($response->headers->get('Content-Security-Policy'))
        ->toContain("default-src 'self'")
        ->toContain("object-src 'none'")
        ->toContain("frame-ancestors 'none'")
        ->toContain("form-action 'self'")
        ->toContain("base-uri 'self'");
})->with([
    'home page' => '/',
    'recipe listing' => '/recipes',
    'login page' => '/login',
    'privacy notice' => '/privacy',
    'JSON search API' => '/api/recipes',
]);

it('never lets inline scripts run', function () {
    $policy = $this->get('/recipes')->headers->get('Content-Security-Policy');

    preg_match('/script-src ([^;]+)/', $policy, $scriptSources);

    expect($scriptSources[1])->not->toContain("'unsafe-inline'")
        ->and($scriptSources[1])->not->toContain("'unsafe-eval'");
});

it('gives every script and style tag on the page the nonce the policy allows', function () {
    $response = $this->get('/login');

    preg_match("/'nonce-([^']+)'/", $response->headers->get('Content-Security-Policy'), $policyNonce);
    preg_match_all('/<(?:script|style)\b[^>]*>/', $response->getContent(), $tags);
    preg_match_all('/<(?:script|style)\b[^>]*\bnonce="([^"]+)"/', $response->getContent(), $noncedTags);

    expect($policyNonce[1] ?? null)->not->toBeNull()
        ->and($tags[0])->not->toBeEmpty()
        ->and($noncedTags[0])->toHaveCount(count($tags[0]))
        ->and(array_unique($noncedTags[1]))->toBe([$policyNonce[1]]);
});

it('uses a fresh nonce for every response', function () {
    $nonceOf = function (string $path): string {
        preg_match("/'nonce-([^']+)'/", $this->get($path)->headers->get('Content-Security-Policy'), $match);

        return $match[1];
    };

    expect($nonceOf('/login'))->not->toBe($nonceOf('/login'));
});

it('tells browsers to stay on HTTPS only when the request came over HTTPS', function () {
    $this->get('http://localhost/privacy')->assertHeaderMissing('Strict-Transport-Security');
    $this->get('https://localhost/privacy')->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
});
