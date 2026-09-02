<?php

use App\Models\User;

test('the login screen can be rendered', function () {
    $this->get(route('login'))->assertOk();
});

test('a user can log in with the correct password', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
});

test('a user cannot log in with an incorrect password', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('login is throttled after five failed attempts', function () {
    $user = User::factory()->create();

    foreach (range(1, 5) as $attempt) {
        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);
    }

    // The sixth attempt is rejected even though the password is now correct.
    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('a user can log out', function () {
    $response = $this->actingAs(User::factory()->create())
        ->post(route('logout'));

    $response->assertRedirect(route('home'));
    $this->assertGuest();
});

test('guests are redirected to login from the dashboard', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('logged in users can reach the dashboard', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk();
});
