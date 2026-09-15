<x-layouts.app title="Log in to your account">
    <section class ="grid place-items-center content-center gap-8 h-screen">


        <section>
            <h1 class="text-2xl font-bold mb-6 text-center">Log in to your account </h1>
            <h2 class="text-sm text-[#706f6c] dark:text-[#A1A09A] mb-6 text-center">Enter your email and password below to log in</h2>
        </section>

        <form method="POST" action="{{ route('login') }}" 
        class="flex 
        flex-col 
        gap-6 
        border-2 
        m-8 
        shadow-md 
        rounded-md 
        p-8
        w-128
        "
        id = "loginForm"
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
                <p class="text-sm text-[#d32903] dark:text-[#FF4433] hidden" id="loginEmailError">
                    Please enter a valid email address. 
                </p>
                <x-input-error field="email" class="server-error" />
            </div>
            {{-- PASSWORD ---}}
            <div class="grid gap-2">
                <x-input-label for="password">Password</x-input-label>

                <x-text-input
                    id="password"
                    type="password"
                    name="password"
                    placeholder="Password"
                    autocomplete="current-password"
                    required
                    :aria-invalid="$errors->has('password') ? 'true' : null"
                    :aria-describedby="$errors->has('password') ? 'password-error' : null"
                />
                <p class="text-sm text-[#d32903] dark:text-[#FF4433] hidden" id="loginPasswordError">
                    Please enter your password.
                </p>

                <x-input-error field="password" class="server-error" />
            </div>
            {{-- REMEMBER ME ---}}
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
            <a href="{{ route('register') }}" class="font-medium underline underline-offset-4 text-[#d32903] dark:text-[#FF4433]">
                Sign up
            </a>
        </p>
</section>
</x-layouts.app>
