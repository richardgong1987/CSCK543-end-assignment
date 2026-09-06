@props(['name', 'value', 'checked' => false])

<label class="flex items-center gap-2 text-sm">
    <input
        type="checkbox"
        name="{{ $name }}"
        value="{{ $value }}"
        @checked($checked)
        class="rounded-sm border-[#19140035] dark:border-[#3E3E3A]"
    >
    {{ $slot }}
</label>
