<?php

namespace Tests\Feature;

use App\Filament\Resources\Posts\Pages\EditPost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Riwayat versi setelah kolom naskah menjadi translatable.
 *
 * Dua hal yang gampang rusak diam-diam di sini, dan keduanya pernah rusak:
 * snapshot yang cuma menyimpan satu bahasa, dan penyimpanan tanpa perubahan
 * yang tetap melahirkan versi baru.
 */
class PostRevisionTranslationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Draf tanpa gambar sampul.
     *
     * Draf karena gambar sampul wajib pada artikel yang sudah terbit, dan tes
     * ini tidak sedang menguji aturan itu. Tanpa gambar karena Livewire::test
     * tidak menghidrasi FileUpload seperti peramban: berkas yang sudah ada di
     * record tetap datang sebagai null dari formulir, sehingga cover_image
     * selalu terlihat berubah dan menutupi apa yang sebenarnya diuji di sini.
     *
     * ->fresh() supaya nilai bawaan kolom (noindex) ikut termuat; tanpa itu
     * snapshot merekam null dan penulisan ulangnya melanggar NOT NULL.
     */
    private function artikel(): Post
    {
        return Post::factory()->draft()->tanpaGambar()->create([
            'title' => ['id' => 'Judul Indonesia', 'en' => 'English Title'],
            'excerpt' => ['id' => 'Ringkasan Indonesia'],
            'body' => ['id' => '<p>Isi Indonesia.</p>'],
        ])->fresh();
    }

    public function test_a_revision_snapshot_keeps_every_locale(): void
    {
        $post = $this->artikel();

        $post->recordRevision('uji');

        $snapshot = $post->revisions()->latest('version')->first()->snapshot;

        // ->only() dulu dipakai di sini, dan ia memanggil accessor: yang
        // tersimpan hanya bahasa yang aktif, sehingga mengembalikan versi
        // lama diam-diam membuang terjemahan Inggris.
        $this->assertSame(
            ['id' => 'Judul Indonesia', 'en' => 'English Title'],
            $snapshot['title'],
        );
    }

    public function test_restoring_a_revision_brings_back_both_locales(): void
    {
        $post = $this->artikel();
        $post->recordRevision('sebelum diubah');

        $post->update(['title' => ['id' => 'Judul Baru', 'en' => 'New Title']]);

        $snapshot = $post->revisions()->orderBy('version')->first()->snapshot;
        $post->forceFill(collect($snapshot)->only(Post::REVISIONED_FIELDS)->all())->save();

        $this->assertSame(
            ['id' => 'Judul Indonesia', 'en' => 'English Title'],
            $post->fresh()->getTranslations('title'),
        );
    }

    /**
     * Membuka artikel lalu menekan Simpan tanpa mengubah apa pun tidak boleh
     * menambah versi.
     *
     * Formulir mengirim satu kunci per tab bahasa — tab teks kosong sebagai
     * null, RichEditor kosong sebagai "<p></p>" — sedangkan getTranslations()
     * tidak memuat bahasa yang tidak pernah diisi. Tanpa keduanya disamakan
     * lebih dulu, tiap penyimpanan terlihat mengubah excerpt, seo_title, dan
     * meta_description sekaligus.
     */
    public function test_saving_without_changes_creates_no_revision(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $post = $this->artikel();

        Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(0, $post->revisions()->count());
    }

    public function test_adding_an_english_translation_creates_one_revision(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $post = $this->artikel();

        Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
            ->fillForm(['excerpt' => ['id' => 'Ringkasan Indonesia', 'en' => 'English excerpt']])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(1, $post->revisions()->count());
        $this->assertSame(
            ['id' => 'Ringkasan Indonesia', 'en' => 'English excerpt'],
            $post->fresh()->getTranslations('excerpt'),
        );
    }
}
