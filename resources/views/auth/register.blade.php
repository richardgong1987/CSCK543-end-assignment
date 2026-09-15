
<x-layouts.app title="Create an account" >

    <section class="grid place-items-center content-center gap-8 h-screen">

        <section class="max-w-md">
            <h1 class="text-3xl text-left">Register</h1>
            <h2 class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Enter your details below to create your account</h2>
        </section>
        <form
        method="POST"
        action="{{ route('register') }}"
        class="
            flex
            flex-col
            gap-6
            mx-auto
            max-w-md
            border
            rounded-md
            p-8
            w-128"
        id="registerForm"
        novalidate
        >
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
                    :aria-invalid="$errors->has('name') ? 'true' : null"
                    :aria-describedby="$errors->has('name') ? 'name-error' : null"
                />
                {{--JavaScript error message (hidden) --}}
                <p class="text-sm text-[#d32903] dark:text-[#FF4433] hidden" id="nameError" > Please enter your name</p>
                {{-- Server-side error message --}}
                <x-input-error field="name" class="server-error" />
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
                    :aria-invalid="$errors->has('email') ? 'true' : null"
                    :aria-describedby="$errors->has('email') ? 'email-error' : null"
                />
                {{-- JavaScript Error Message --}}
                <p class="text-sm text-[#d32903] dark:text-[#FF4433] hidden" id="emailError">Please enter a valid email</p>

                {{-- Server-side error message--}}
                <x-input-error field="email" class="server-error" />
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
                    :aria-invalid="$errors->has('password') ? 'true' : null"
                    :aria-describedby="$errors->has('password') ? 'password-error' : null"
                />
                {{-- JavaScript error message --}}
                <p class="text-sm text-[#d32903] dark:text-[#FF4433] hidden" id="passwordError">
                    Password must be at least 8 characters.
                </p>
                {{-- Server-side error message --}}
                <x-input-error field="password" class="server-error" />
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
                    :aria-invalid="$errors->has('password_confirmation') ? 'true' : null"
                    :aria-describedby="$errors->has('password_confirmation') ? 'password_confirmation-error' : null"
                />
                {{-- JavaScript error message --}}
                <p class="text-sm text-[#d32903] dark:text-[#FF4433] hidden" id="confirmError">
                    Passwords do not match.
                </p>
                {{-- Server-side error message --}}
                <x-input-error field="password_confirmation" class="server-error" />
            </div>

            <x-primary-button class="w-full">Create account</x-primary-button>
        </form>
        {{-- Link to Login Page --}}
        <p class="text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
            Already have an account?
            <a href="{{ route('login') }}" class="font-medium underline underline-offset-4 text-[#d32903] dark:text-[#FF4433]">
                Log in
            </a>
        </p>
    </section>
</x-layouts.app>

