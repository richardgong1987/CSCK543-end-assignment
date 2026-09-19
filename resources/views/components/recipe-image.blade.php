@props([
    // A recipe photo's JPEG path under public/, such as a recipe's image_path.
    'path',
    // How wide the photo is shown, so the browser downloads the smallest copy that fits.
    'sizes',
])

@php
    // Each JPEG has WebP copies 416, 640 and 832 pixels wide beside it (docs/performance.md,
    // "Recipe photos"). Pages show only the WebP copies; the JPEG stays for the JSON API.
    $webpUrl = fn (int $width) => asset(preg_replace('/\.jpg$/', "-{$width}.webp", $path));
@endphp

<img
    src="{{ $webpUrl(832) }}"
    srcset="{{ $webpUrl(416) }} 416w, {{ $webpUrl(640) }} 640w, {{ $webpUrl(832) }} 832w"
    sizes="{{ $sizes }}"
    width="832"
    height="468"
    {{ $attributes }}
>
