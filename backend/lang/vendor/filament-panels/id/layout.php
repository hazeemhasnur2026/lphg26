<?php

/*
 * Tambalan untuk satu kunci yang hilang dari terjemahan Indonesia bawaan
 * Filament.
 *
 * Berkas id/layout.php milik paket punya 'open_database_notifications.label'
 * tetapi tidak punya 'label_with_unread_count'. Begitu ada pemberitahuan yang
 * belum dibaca, tombol loncengnya memakai kunci kedua, dan yang tidak
 * diterjemahkan akan dikembalikan apa adanya: aria-label tombol itu berbunyi
 * "filament-panels::layout.actions.open_database_notifications.label_with_unread_count".
 *
 * Tidak terlihat mata, dan itu sebabnya mudah lolos — yang membacakannya adalah
 * pembaca layar, kepada orang yang paling bergantung padanya.
 *
 * Laravel menggabungkan berkas ini dengan milik paket, bukan menggantikannya,
 * jadi cukup menulis kunci yang kurang saja. Bahasa Indonesia tidak mengubah
 * bentuk kata benda menurut jumlah, jadi kedua cabangnya sama.
 */
return [

    'actions' => [

        'open_database_notifications' => [
            'label_with_unread_count' => '{1} Notifikasi, :count belum dibaca|[2,*] Notifikasi, :count belum dibaca',
        ],

    ],

];
