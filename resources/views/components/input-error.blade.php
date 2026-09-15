@props(['field', 'bag' => 'default'])

{{-- The id lets the field point at this message with aria-describedby. It is "{field}-error"
     unless the page passes its own, as it must when two forms share a field name. --}}
@error($field, $bag)
    <p {{ $attributes->merge(['id' => $field.'-error', 'class' => 'text-sm text-[#d32903] dark:text-[#FF4433]']) }}>
        {{ $message }}
    </p>
@enderror
