<?php

use App\Models\Recipe;
use App\Models\User;
use Database\Seeders\RecipeSeeder;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Support\Facades\Hash;

// The user factory gives every user the password "password".

it('shows the account settings page filled with the user\'s current details', function () {
    $user = User::factory()->create(['name' => 'Ada Lovelace', 'email' => 'ada@example.com']);

    $this->actingAs($user)
        ->get(route('account.edit'))
        ->assertOk()
        ->assertSee('value="Ada Lovelace"', escape: false)
        ->assertSee('value="ada@example.com"', escape: false);
});

it('links to the account settings from the account page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertSee(route('account.edit'));
});

it('sends guests to the login page instead of the account settings', function () {
    $this->get(route('account.edit'))->assertRedirect(route('login'));
    $this->patch(route('account.update'), ['name' => 'Ada', 'email' => 'ada@example.com'])->assertRedirect(route('login'));
    $this->put(route('account.password.update'))->assertRedirect(route('login'));
    $this->delete(route('account.destroy'))->assertRedirect(route('login'));
});

it('saves a new name and email address', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->from(route('account.edit'))
        ->patch(route('account.update'), ['name' => 'Ada Lovelace', 'email' => 'ada@example.com']);

    $response->assertRedirect(route('account.edit'));
    $response->assertSessionHas('status', 'Your details have been saved.');

    expect($user->fresh())
        ->name->toBe('Ada Lovelace')
        ->email->toBe('ada@example.com');
});

it('lets the user keep their own email address while changing their name', function () {
    $user = User::factory()->create(['email' => 'ada@example.com']);

    $response = $this->actingAs($user)
        ->patch(route('account.update'), ['name' => 'Ada King', 'email' => 'ada@example.com']);

    $response->assertValid();

    expect($user->fresh()->name)->toBe('Ada King');
});

it('rejects an email address that another user already has', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create(['email' => 'ada@example.com']);

    $response = $this->actingAs($user)
        ->patch(route('account.update'), ['name' => 'Ada', 'email' => 'taken@example.com']);

    $response->assertInvalid(['email' => 'already been taken']);

    expect($user->fresh()->email)->toBe('ada@example.com');
});

it('rejects invalid details', function (array $invalidDetails, string $field, string $expectedMessage) {
    $user = User::factory()->create(['name' => 'Ada', 'email' => 'ada@example.com']);

    $response = $this->actingAs($user)->patch(route('account.update'), [
        'name' => 'Ada',
        'email' => 'ada@example.com',
        ...$invalidDetails,
    ]);

    $response->assertInvalid([$field => $expectedMessage]);

    expect($user->fresh())
        ->name->toBe('Ada')
        ->email->toBe('ada@example.com');
})->with([
    'missing name' => [['name' => ''], 'name', 'required'],
    'name longer than 255 characters' => [['name' => str_repeat('a', 256)], 'name', 'greater than 255'],
    'missing email' => [['email' => ''], 'email', 'required'],
    'malformed email' => [['email' => 'not-an-email'], 'email', 'valid email'],
]);

it('changes the password when the current password is right', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->from(route('account.edit'))
        ->put(route('account.password.update'), [
            'current_password' => 'password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

    $response->assertRedirect(route('account.edit'));
    $response->assertSessionHas('status', 'Your password has been changed.');

    expect(Hash::check('new-password-123', $user->fresh()->password))->toBeTrue();
});

it('keeps the old password when a password change breaks a rule', function (array $invalidInput, string $field, string $expectedMessage) {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->put(route('account.password.update'), [
        'current_password' => 'password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
        ...$invalidInput,
    ]);

    $response->assertInvalid([$field => $expectedMessage], 'updatePassword');

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
})->with([
    'wrong current password' => [['current_password' => 'wrong-password'], 'current_password', 'incorrect'],
    'missing current password' => [['current_password' => ''], 'current_password', 'required'],
    'new password shorter than 8 characters' => [['password' => 'short12', 'password_confirmation' => 'short12'], 'password', 'at least 8'],
    'new password not confirmed' => [['password_confirmation' => 'something-else'], 'password', 'confirmation'],
]);

it('deletes the account with its favourites and ratings, and logs the user out', function () {
    $this->seed([ReferenceDataSeeder::class, RecipeSeeder::class]);
    $user = User::factory()->create();
    $recipe = Recipe::first();
    $user->favouriteRecipes()->attach($recipe);
    $user->ratings()->create(['recipe_id' => $recipe->id, 'overall' => 4]);

    $response = $this->actingAs($user)->delete(route('account.destroy'), ['password' => 'password']);

    $response->assertRedirect(route('home'));
    $response->assertSessionHas('status', 'Your account has been deleted.');
    $this->assertGuest();

    expect(User::find($user->id))->toBeNull();
    $this->assertDatabaseMissing('favourites', ['user_id' => $user->id]);
    $this->assertDatabaseMissing('ratings', ['user_id' => $user->id]);
});

it('keeps the account when deletion is not confirmed with the right password', function (string $password, string $expectedMessage) {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->delete(route('account.destroy'), ['password' => $password]);

    $response->assertInvalid(['password' => $expectedMessage], 'deleteAccount');
    $this->assertAuthenticatedAs($user);

    expect(User::find($user->id))->not->toBeNull();
})->with([
    'wrong password' => ['wrong-password', 'incorrect'],
    'no password' => ['', 'required'],
]);

it('shows a form\'s error beside that form\'s own password field', function () {
    // Both the password form and the delete form have a "password" field, so their errors must not mix.
    $this->actingAs(User::factory()->create())
        ->from(route('account.edit'))
        ->followingRedirects()
        ->delete(route('account.destroy'), ['password' => 'wrong-password'])
        ->assertSee('id="delete_password-error"', escape: false)
        ->assertSee('aria-describedby="delete_password-error"', escape: false)
        ->assertDontSee('id="new_password-error"', escape: false);
});
