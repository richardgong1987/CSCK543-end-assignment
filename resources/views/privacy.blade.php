@php
    $cardClasses = 'rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]';
    $mutedClasses = 'text-[#706f6c] dark:text-[#A1A09A]';
@endphp

<x-layouts.app title="Privacy notice">
    <article class="max-w-prose space-y-10 text-sm leading-relaxed">
        <header>
            <h1 class="mb-3 text-2xl font-medium">Privacy notice</h1>
            <p class="{{ $mutedClasses }}">
                {{ config('app.name') }} is a student project built for the CSCK543 module at the University of
                Liverpool. It is run locally for demonstration and assessment, not as a public service. This page
                explains what it stores about you, why, and how to remove it.
            </p>
        </header>

        <section aria-labelledby="stored-heading">
            <h2 id="stored-heading" class="mb-3 text-lg font-medium">What we store, and why</h2>

            <div class="overflow-x-auto {{ $cardClasses }}">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]">
                            <th scope="col" class="py-2 pr-4 font-medium">What</th>
                            <th scope="col" class="py-2 font-medium">Why</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                        <tr>
                            <th scope="row" class="py-2 pr-4 align-top font-normal">Your name</th>
                            <td class="py-2">To show on your account page.</td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-2 pr-4 align-top font-normal">Your email address</th>
                            <td class="py-2">To log you in, and to send a password reset link when you ask for one. It is never shown to other users.</td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-2 pr-4 align-top font-normal">Your password</th>
                            <td class="py-2">To log you in. It is stored only as a one-way hash, so nobody, including us, can read it.</td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-2 pr-4 align-top font-normal">Recipes you save and rate</th>
                            <td class="py-2">To list them on your account page. Your scores also count towards each recipe's average rating, which everyone can see without your name attached.</td>
                        </tr>
                        <tr>
                            <th scope="row" class="py-2 pr-4 align-top font-normal">Your login session</th>
                            <td class="py-2">To keep you logged in between pages. While a session lasts we also hold your IP address and your browser's description of itself.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p class="mt-3 {{ $mutedClasses }}">
                That is all. We ask for nothing else, use no analytics or advertising, and do not share your details with anyone.
            </p>
        </section>

        <section aria-labelledby="cookies-heading">
            <h2 id="cookies-heading" class="mb-3 text-lg font-medium">Cookies</h2>

            <ul class="list-disc space-y-2 pl-5">
                <li><code>{{ config('session.cookie') }}</code> identifies your session, so you stay logged in.</li>
                <li><code>XSRF-TOKEN</code> protects the forms from being submitted by other websites.</li>
                <li>A "remember me" cookie is set only if you tick <em>Remember me</em> when you log in.</li>
            </ul>

            <p class="mt-3 {{ $mutedClasses }}">All of them are needed for the site to work; none of them tracks you.</p>
        </section>

        <section aria-labelledby="delete-heading">
            <h2 id="delete-heading" class="mb-3 text-lg font-medium">Removing your data</h2>

            <p class="mb-3">
                You can delete your account at any time from
                @auth
                    <a href="{{ route('account.edit') }}" class="underline underline-offset-4">your account settings</a>.
                @else
                    your account settings once you have logged in.
                @endauth
                This removes your account, your saved recipes and your ratings straight away.
            </p>

            <p class="{{ $mutedClasses }}">
                The accounts used in demonstrations and screenshots are fictional. Rebuilding the database, which we do
                to return it to a known state, deletes every account, including any registered by hand.
            </p>
        </section>

        <section aria-labelledby="recipes-heading">
            <h2 id="recipes-heading" class="mb-3 text-lg font-medium">The recipes</h2>

            <p>
                The recipes, their text and their images come from BBC Food and are reproduced for the educational purpose
                of this assignment only. Each recipe page links to its original.
            </p>
        </section>
    </article>
</x-layouts.app>
