<x-layouts.auth
    title="Create an account"
    description="Enter your details below to create your account"
>
    <form method="POST" action="{{ route('register') }}" class="flex flex-col gap-6">
        @csrf

        <div class="grid gap-2">
            <x-input-label for="name">Name</x-input-label>

            <x-text-input
                id="name"
                name="name"
                value="{{ old('name') }}"
                placeholder="Full name"
                autocomplete="name"
                required
                autofocus
            />

            <x-input-error field="name" />
        </div>

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
            />

            <x-input-error field="email" />
        </div>

        <div class="grid gap-2">
            <x-input-label for="password">Password</x-input-label>

            <x-text-input
                id="password"
                type="password"
                name="password"
                placeholder="Password"
                autocomplete="new-password"
                required
            />

            <x-input-error field="password" />
        </div>

        <div class="grid gap-2">
            <x-input-label for="password_confirmation">Confirm password</x-input-label>

            <x-text-input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                placeholder="Confirm password"
                autocomplete="new-password"
                required
            />

            <x-input-error field="password_confirmation" />
        </div>

        <x-primary-button class="w-full">Create account</x-primary-button>
    </form>

    <p class="text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
        Already have an account?
        <a href="{{ route('login') }}" class="font-medium underline underline-offset-4 text-[#f53003] dark:text-[#FF4433]">
            Log in
        </a>
    </p>
</x-layouts.auth>
