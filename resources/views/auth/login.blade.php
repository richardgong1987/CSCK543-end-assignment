<x-layouts.app title="Log in to your account">
    <div class ="max-w-md mx-auto mt-40">
        <h1 class="text-2xl font-bold mb-6 text-center">Log in to your account </h1>
        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mb-6 text-center">Enter your email and password below to log in"</p>

        <form method="POST" action="{{ route('login') }}" 
        class="flex 
        flex-col 
        gap-6 
        border-2 
        m-8 
        shadow-md 
        rounded-md 
        p-8">
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
                    autocomplete="current-password"
                    required
                />

                <x-input-error field="password" />
            </div>

            <label for="remember" class="flex items-center gap-3 text-sm">
                <input
                    id="remember"
                    type="checkbox"
                    name="remember"
                    class="rounded-sm border-[#19140035] dark:border-[#3E3E3A]"
                >
                Remember me
            </label>

            <x-primary-button class="w-full">Log in</x-primary-button>
        </form>

        <p class="text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
            Don't have an account?
            <a href="{{ route('register') }}" class="font-medium underline underline-offset-4 text-[#f53003] dark:text-[#FF4433]">
                Sign up
            </a>
        </p>
    </div>
</x-layouts.auth>
