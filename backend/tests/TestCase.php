<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        /*
         * public/build tidak dilacak git. Hasil build dibuat saat deploy. Tanpa
         * ini, tiap tes yang merender halaman akan gagal di clone yang belum
         * menjalankan `npm run build`, dan kegagalannya tidak ada hubungannya
         * dengan apa yang diuji. Keberadaan CSS yang benar diperiksa
         * tools/check_css_coverage.py terhadap situs yang sungguh berjalan.
         */
        $this->withoutVite();
    }
}
