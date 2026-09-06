{{-- Width is left to the caller: this button sits in a form column as often as it spans one. --}}
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'rounded-sm border border-black bg-[#1b1b18] px-5 py-2 text-sm leading-normal text-white hover:border-black hover:bg-black dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white']) }}>
    {{ $slot }}
</button>
