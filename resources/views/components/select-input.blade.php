<select {{ $attributes->merge(['class' => 'w-full rounded-sm border border-[#91918f] bg-white px-3 py-2 text-sm focus:border-[#1b1b18] focus:outline-none dark:border-[#676763] dark:bg-[#161615] dark:focus:border-[#EDEDEC]']) }}>
    {{ $slot }}
</select>
