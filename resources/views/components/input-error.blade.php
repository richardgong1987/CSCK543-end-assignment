@props(['field'])

{{-- The id lets the field point at this message with aria-describedby: "{field}-error". --}}
@error($field)
    <p {{ $attributes->merge(['id' => $field.'-error', 'class' => 'text-sm text-[#d32903] dark:text-[#FF4433]']) }}>
        {{ $message }}
    </p>
@enderror
