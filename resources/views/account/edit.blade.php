@php
    $cardClasses = 'mb-10 rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]';
    $clientErrorClasses = 'hidden text-sm text-[#d32903] dark:text-[#FF4433]';
    $passwordErrors = $errors->updatePassword;
    $deleteErrors = $errors->deleteAccount;
@endphp

<x-layouts.app title="Account settings">
    <nav aria-label="Breadcrumb" class="mb-6 text-sm">
        <a href="{{ route('dashboard') }}" class="underline-offset-4 hover:underline">
            &larr; Your account
        </a>
    </nav>

    <h1 class="mb-8 text-2xl font-medium">Account settings</h1>

    {{-- Each form's JavaScript checks live in resources/js/account.validation.js. --}}
    <section aria-labelledby="details-heading" class="{{ $cardClasses }}">
        <h2 id="details-heading" class="mb-4 text-lg font-medium">Your details</h2>

        <form id="accountDetailsForm" method="POST" action="{{ route('account.update') }}" class="grid max-w-md gap-6" novalidate>
            @csrf
            @method('PATCH')

            <div class="grid gap-2">
                <x-input-label for="name">Name</x-input-label>

                <x-text-input
                    id="name"
                    name="name"
                    value="{{ old('name', $user->name) }}"
                    autocomplete="name"
                    required
                    maxlength="255"
                    :aria-invalid="$errors->has('name') ? 'true' : null"
                    :aria-describedby="$errors->has('name') ? 'name-error' : null"
                />
                <p id="nameError" class="{{ $clientErrorClasses }}">Please enter your name.</p>
                <x-input-error field="name" class="server-error" />
            </div>

            <div class="grid gap-2">
                <x-input-label for="email">Email address</x-input-label>

                <x-text-input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email', $user->email) }}"
                    autocomplete="email"
                    required
                    maxlength="255"
                    :aria-invalid="$errors->has('email') ? 'true' : null"
                    :aria-describedby="$errors->has('email') ? 'email-error' : null"
                />
                <p id="emailError" class="{{ $clientErrorClasses }}">Please enter a valid email address.</p>
                <x-input-error field="email" class="server-error" />
            </div>

            <div>
                <x-primary-button>Save details</x-primary-button>
            </div>
        </form>
    </section>

    <section aria-labelledby="password-heading" class="{{ $cardClasses }}">
        <h2 id="password-heading" class="mb-4 text-lg font-medium">Change your password</h2>

        <form id="accountPasswordForm" method="POST" action="{{ route('account.password.update') }}" class="grid max-w-md gap-6" novalidate>
            @csrf
            @method('PUT')

            <div class="grid gap-2">
                <x-input-label for="current_password">Current password</x-input-label>

                <x-text-input
                    id="current_password"
                    type="password"
                    name="current_password"
                    autocomplete="current-password"
                    required
                    :aria-invalid="$passwordErrors->has('current_password') ? 'true' : null"
                    :aria-describedby="$passwordErrors->has('current_password') ? 'current_password-error' : null"
                />
                <p id="currentPasswordError" class="{{ $clientErrorClasses }}">Please enter your current password.</p>
                <x-input-error field="current_password" bag="updatePassword" class="server-error" />
            </div>

            <div class="grid gap-2">
                <x-input-label for="new_password">New password</x-input-label>

                <x-text-input
                    id="new_password"
                    type="password"
                    name="password"
                    autocomplete="new-password"
                    required
                    :aria-invalid="$passwordErrors->has('password') ? 'true' : null"
                    :aria-describedby="$passwordErrors->has('password') ? 'new_password-error' : null"
                />
                <p id="newPasswordError" class="{{ $clientErrorClasses }}">Your new password must be at least 8 characters.</p>
                <x-input-error field="password" bag="updatePassword" id="new_password-error" class="server-error" />
            </div>

            <div class="grid gap-2">
                <x-input-label for="new_password_confirmation">Confirm new password</x-input-label>

                <x-text-input
                    id="new_password_confirmation"
                    type="password"
                    name="password_confirmation"
                    autocomplete="new-password"
                    required
                />
                <p id="newPasswordConfirmError" class="{{ $clientErrorClasses }}">Passwords do not match.</p>
            </div>

            <div>
                <x-primary-button>Change password</x-primary-button>
            </div>
        </form>
    </section>

    <section aria-labelledby="delete-heading" class="{{ $cardClasses }}">
        <h2 id="delete-heading" class="mb-2 text-lg font-medium">Delete your account</h2>

        <p class="mb-4 max-w-prose text-sm text-[#706f6c] dark:text-[#A1A09A]">
            This permanently deletes your account, your saved recipes and your ratings, and it cannot be undone.
            Enter your password to confirm.
        </p>

        <form id="deleteAccountForm" method="POST" action="{{ route('account.destroy') }}" class="grid max-w-md gap-6" novalidate>
            @csrf
            @method('DELETE')

            <div class="grid gap-2">
                <x-input-label for="delete_password">Password</x-input-label>

                <x-text-input
                    id="delete_password"
                    type="password"
                    name="password"
                    autocomplete="current-password"
                    required
                    :aria-invalid="$deleteErrors->has('password') ? 'true' : null"
                    :aria-describedby="$deleteErrors->has('password') ? 'delete_password-error' : null"
                />
                <p id="deletePasswordError" class="{{ $clientErrorClasses }}">Please enter your password to confirm.</p>
                <x-input-error field="password" bag="deleteAccount" id="delete_password-error" class="server-error" />
            </div>

            <div>
                <button type="submit"
                    class="cursor-pointer rounded-sm border border-[#d32903] px-5 py-2 text-sm leading-normal font-medium text-[#d32903] hover:bg-[#d32903] hover:text-white dark:border-[#FF4433] dark:text-[#FF4433] dark:hover:bg-[#FF4433] dark:hover:text-[#0a0a0a]">
                    Delete account
                </button>
            </div>
        </form>
    </section>
</x-layouts.app>
