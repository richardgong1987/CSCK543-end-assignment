<x-layouts.app title="Forgot your password">
    <section class="grid place-items-center content-center gap-8">
        <section class="max-w-md text-center">
            <h1 class="mb-2 text-2xl font-bold">Forgot your password?</h1>
            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                Enter the email address you registered with, and we will send you a link to choose a new password.
            </p>
        </section>

        {{-- The JavaScript checks live in resources/js/password-reset.validation.js. --}}
        <form
            id="forgotPasswordForm"
            method="POST"
            action="{{ route('password.email') }}"
            class="flex w-full max-w-md flex-col gap-6 rounded-md border-2 p-8 shadow-md"
            novalidate
        >
            @csrf

            <div class="grid gap-2">
                <x-input-label for="email">Email address</x-input-label>

                <x-text-input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="email@example.com"
                    autocomplete="email"
                    required
                    autofocus
                    :aria-invalid="$errors->has('email') ? 'true' : null"
                    :aria-describedby="$errors->has('email') ? 'email-error' : null"
                />
                <p id="forgotEmailError" class="hidden text-sm text-[#d32903] dark:text-[#FF4433]">
                    Please enter a valid email address.
                </p>
                <x-input-error field="email" class="server-error" />
            </div>

            <x-primary-button class="w-full">Email me a reset link</x-primary-button>
        </form>

        <p class="text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
            Remembered it?
            <a href="{{ route('login') }}" class="font-medium underline underline-offset-4 text-[#d32903] dark:text-[#FF4433]">
                Log in
            </a>
        </p>
    </section>
</x-layouts.app>
