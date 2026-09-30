{{--
    Pita gradien merek: Navy → Gold → Emerald → Merah → Navy.

    Sebelumnya string yang sama persis disalin identik di tiga berkas
    (posts/show, page-hero, partials/announcement). Palet korporat baru saja
    diganti sekali di sesi sebelumnya, dan pekerjaan itu berarti mengedit tiga
    tempat secara manual sambil mengandalkan ingatan bahwa ketiganya harus
    tetap sama. Satu komponen berarti satu tempat untuk perubahan berikutnya.
--}}
@props(['tinggi' => 'h-1.5'])

<div {{ $attributes->merge(['class' => "{$tinggi} w-full"]) }}
     style="background:linear-gradient(90deg,#0F2747,#C9A227,#007A53,#E03633,#0F2747)"></div>
