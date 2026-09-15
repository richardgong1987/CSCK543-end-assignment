<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

// The user factory gives every user the password "password".

test('the forgot password screen can be rendered', function () {
    $this->get(route('password.request'))->assertOk();
});

test('the login page links to the forgot password screen', function () {
    $this->get(route('login'))->assertSee(route('password.request'));
});

test('logged in users are redirected away from the password reset screens', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('password.request'))->assertRedirect();
    $this->get(route('password.reset', ['token' => 'any-token']))->assertRedirect();
});

test('a reset link is emailed to a registered address', function () {
    Notification::fake();
    $user = User::factory()->create();

    $response = $this->from(route('password.request'))
        ->post(route('password.email'), ['email' => $user->email]);

    $response->assertRedirect(route('password.request'));
    $response->assertSessionHas('status', 'If an account exists for that email address, we have sent it a link to reset the password.');

    Notification::assertSentTo($user, ResetPassword::class);
});

test('an unregistered address gets the same reply and no email', function () {
    Notification::fake();

    $response = $this->post(route('password.email'), ['email' => 'nobody@example.com']);

    // Identical to the registered case, so the form cannot reveal who has an account.
    $response->assertSessionHasNoErrors();
    $response->assertSessionHas('status', 'If an account exists for that email address, we have sent it a link to reset the password.');

    Notification::assertNothingSent();
});

test('the forgot password form rejects invalid input', function (string $email, string $expectedMessage) {
    $this->post(route('password.email'), ['email' => $email])
        ->assertInvalid(['email' => $expectedMessage]);
})->with([
    'missing email' => ['', 'required'],
    'malformed email' => ['not-an-email', 'valid email'],
]);

test('requests for reset links are rate limited', function () {
    Notification::fake();

    foreach (range(1, 6) as $attempt) {
        $this->post(route('password.email'), ['email' => 'nobody@example.com'])->assertRedirect();
    }

    $this->post(route('password.email'), ['email' => 'nobody@example.com'])->assertTooManyRequests();
});

test('the emailed link opens the form for choosing a new password', function () {
    Notification::fake();
    $user = User::factory()->create(['email' => 'ada@example.com']);

    $this->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
        $this->get($notification->toMail($user)->actionUrl)
            ->assertOk()
            ->assertSee('value="ada@example.com"', escape: false)
            ->assertSee('value="'.$notification->token.'"', escape: false);

        return true;
    });
});

test('a valid reset link sets the new password', function () {
    $user = User::factory()->create(['email' => 'ada@example.com']);
    $token = Password::createToken($user);

    $response = $this->post(route('password.store'), [
        'token' => $token,
        'email' => 'ada@example.com',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('status', 'Your password has been reset. You can now log in with the new password.');
    $this->assertGuest();

    expect(Hash::check('new-password-123', $user->fresh()->password))->toBeTrue();
});

test('a reset link cannot be used twice', function () {
    $user = User::factory()->create(['email' => 'ada@example.com']);
    $token = Password::createToken($user);
    $newPassword = ['password' => 'new-password-123', 'password_confirmation' => 'new-password-123'];

    $this->post(route('password.store'), ['token' => $token, 'email' => 'ada@example.com', ...$newPassword]);

    $this->post(route('password.store'), [
        'token' => $token,
        'email' => 'ada@example.com',
        'password' => 'another-password-456',
        'password_confirmation' => 'another-password-456',
    ])->assertInvalid(['email' => 'invalid or has expired']);

    expect(Hash::check('new-password-123', $user->fresh()->password))->toBeTrue();
});

test('a made-up token does not change the password', function () {
    $user = User::factory()->create(['email' => 'ada@example.com']);

    $this->post(route('password.store'), [
        'token' => 'not-a-real-token',
        'email' => 'ada@example.com',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])->assertInvalid(['email' => 'invalid or has expired']);

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});

test('a token for one account cannot reset another account', function () {
    $user = User::factory()->create(['email' => 'ada@example.com']);
    $otherUser = User::factory()->create(['email' => 'ben@example.com']);

    $this->post(route('password.store'), [
        'token' => Password::createToken($user),
        'email' => 'ben@example.com',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])->assertInvalid(['email' => 'invalid or has expired']);

    expect(Hash::check('password', $otherUser->fresh()->password))->toBeTrue();
});

test('a reset link stops working after 60 minutes', function () {
    $user = User::factory()->create(['email' => 'ada@example.com']);
    $token = Password::createToken($user);

    $this->travel(61)->minutes();

    $this->post(route('password.store'), [
        'token' => $token,
        'email' => 'ada@example.com',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])->assertInvalid(['email' => 'invalid or has expired']);

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});

test('the new password must meet the same rules as registration', function (array $invalidInput, string $field, string $expectedMessage) {
    $user = User::factory()->create(['email' => 'ada@example.com']);

    $this->post(route('password.store'), [
        'token' => Password::createToken($user),
        'email' => 'ada@example.com',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
        ...$invalidInput,
    ])->assertInvalid([$field => $expectedMessage]);

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
})->with([
    'password shorter than 8 characters' => [['password' => 'short12', 'password_confirmation' => 'short12'], 'password', 'at least 8'],
    'password not confirmed' => [['password_confirmation' => 'something-else'], 'password', 'confirmation'],
    'missing email' => [['email' => ''], 'email', 'required'],
]);
