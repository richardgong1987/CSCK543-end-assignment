@props(['field'])

@error($field)
    <p {{ $attributes->merge(['class' => 'text-sm text-[#f53003] dark:text-[#FF4433]']) }}>
        {{ $message }}
    </p>
@enderror
