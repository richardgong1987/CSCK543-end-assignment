@props(['for'])

<label {{ $attributes->merge(['for' => $for, 'class' => 'block text-sm font-medium']) }}>
    {{ $slot }}
</label>
