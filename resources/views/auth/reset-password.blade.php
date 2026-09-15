@php
    $clientErrorClasses = 'hidden text-sm text-[#d32903] dark:text-[#FF4433]';
@endphp

<x-layouts.app title="Choose a new password">
    <section class="grid place-items-center content-center gap-8">
        <section class="max-w-md text-center">
            <h1 class="mb-2 text-2xl font-bold">Choose a new password</h1>
            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                Confirm your email address and enter the new password twice.
            </p>
        </section>

        {{-- The JavaScript checks live in resources/js/password-reset.validation.js. --}}
        <form
            id="resetPasswordForm"
            method="POST"
            action="{{ route('password.store') }}"
            class="flex w-full max-w-md flex-col gap-6 rounded-md border-2 p-8 shadow-md"
            novalidate
        >
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="grid gap-2">
                <x-input-label for="email">Email address</x-input-label>

                <x-text-input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email', $email) }}"
                    autocomplete="email"
                    required
                    :aria-invalid="$errors->has('email') ? 'true' : null"
                    :aria-describedby="$errors->has('email') ? 'email-error' : null"
                />
                <p id="resetEmailError" class="{{ $clientErrorClasses }}">Please enter a valid email address.</p>
                <x-input-error field="email" class="server-error" />
            </div>

            <div class="grid gap-2">
                <x-input-label for="password">New password</x-input-label>

                <x-text-input
                    id="password"
                    type="password"
                    name="password"
                    autocomplete="new-password"
                    required
                    autofocus
                    :aria-invalid="$errors->has('password') ? 'true' : null"
                    :aria-describedby="$errors->has('password') ? 'password-error' : null"
                />
                <p id="resetPasswordError" class="{{ $clientErrorClasses }}">Password must be at least 8 characters.</p>
                <x-input-error field="password" class="server-error" />
            </div>

            <div class="grid gap-2">
                <x-input-label for="password_confirmation">Confirm new password</x-input-label>

                <x-text-input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    autocomplete="new-password"
                    required
                />
                <p id="resetConfirmError" class="{{ $clientErrorClasses }}">Passwords do not match.</p>
            </div>

            <x-primary-button class="w-full">Reset password</x-primary-button>
        </form>

        <p class="text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
            Link expired?
            <a href="{{ route('password.request') }}" class="font-medium underline underline-offset-4 text-[#d32903] dark:text-[#FF4433]">
                Request a new one
            </a>
        </p>
    </section>
</x-layouts.app>
