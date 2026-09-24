@props([
    'src',
    'alt' => '',
    // Lebar tampil gambar, dipakai browser untuk memilih varian srcset. Default
    // konservatif: satu kolom penuh. Kartu dan grid wajib menyebutkan sendiri,
    // kalau tidak browser mengunduh varian yang lebih besar dari perlunya.
    'sizes' => '100vw',
    // width/height intrinsik mencegah layout shift, tapi merusak gambar yang
    // ukurannya ditentukan CSS satu sisi saja. Bisa dimatikan per pemakaian.
    'dimensions' => true,
])
@php
    $entry = \App\Support\ImageVariants::for($src);
    $size = $dimensions && $entry ? ['width' => $entry['width'], 'height' => $entry['height']] : [];
@endphp
@if ($entry)
    {{-- display:contents membuat <picture> tidak ikut menghitung layout, jadi
         posisi, flex, dan grid pada <img> berperilaku sama seperti sebelum
         dibungkus. --}}
    <picture style="display:contents">
        <source type="image/webp" srcset="{{ \App\Support\ImageVariants::srcset($entry) }}" sizes="{{ $sizes }}">
        <img src="{{ $src }}" alt="{{ $alt }}" @foreach ($size as $k => $v) {{ $k }}="{{ $v }}" @endforeach {{ $attributes }}>
    </picture>
@else
    <img src="{{ $src }}" alt="{{ $alt }}" {{ $attributes }}>
@endif
