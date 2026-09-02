<?php

use App\Models\User;

test('the registration screen can be rendered', function () {
    $this->get(route('register'))->assertOk();
});

test('a new user can register and is logged in', function () {
    $response = $this->post(route('register'), [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();

    expect(User::where('email', 'ada@example.com')->exists())->toBeTrue();
});

test('the registered password is hashed, never stored in plain text', function () {
    $this->post(route('register'), [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    expect(User::firstWhere('email', 'ada@example.com')->password)->not->toBe('password');
});

test('registration rejects an email that is already taken', function () {
    User::factory()->create(['email' => 'ada@example.com']);

    $response = $this->post(route('register'), [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('registration rejects a password that is not confirmed', function () {
    $response = $this->post(route('register'), [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'password' => 'password',
        'password_confirmation' => 'different-password',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertGuest();
});

test('logged in users are redirected away from registration', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('register'))
        ->assertRedirect();
});
