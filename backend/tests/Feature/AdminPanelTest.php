<?php

namespace Tests\Feature;

use App\Filament\Resources\BusinessUnits\BusinessUnitResource;
use App\Filament\Resources\Offices\OfficeResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\SlideBanners\SlideBannerResource;
use App\Filament\Resources\Vacancies\VacancyResource;
use App\Models\BusinessUnit;
use App\Models\Office;
use App\Models\Post;
use App\Models\SlideBanner;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Halaman admin membalas 200 untuk tamu juga, yang terjadi hanya pengalihan ke
 * login. Jadi memeriksa status saja tidak membuktikan apa pun tentang form dan
 * tabelnya; semua tes di sini masuk sebagai pengguna yang sudah login.
 */
class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Akun yang dipakai hampir seluruh tes di berkas ini.
     *
     * Harus dinyatakan sebagai admin sejak peran dipecah empat. Bawaan factory
     * adalah Kontributor, dan kontributor hanya melihat naskahnya sendiri —
     * tes tentang tabel dan form akan gagal karena barisnya memang tidak boleh
     * ia lihat, bukan karena panelnya rusak.
     */
    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    /** @return array<string, array{class-string, callable}> */
    public static function resources(): array
    {
        return [
            'artikel' => [PostResource::class, fn () => Post::factory()->create()],
            'unit bisnis' => [BusinessUnitResource::class, fn () => BusinessUnit::factory()->create()],
            'slide' => [SlideBannerResource::class, fn () => SlideBanner::factory()->create()],
            'kantor' => [OfficeResource::class, fn () => Office::factory()->create()],
            'lowongan' => [VacancyResource::class, fn () => Vacancy::factory()->create()],
        ];
    }

    /** @param  class-string  $resource */
    #[DataProvider('resources')]
    public function test_every_resource_lists_creates_and_edits(string $resource, callable $makeRecord): void
    {
        $this->actingAs($this->admin());
        $record = $makeRecord();

        $this->get($resource::getUrl('index'))->assertOk();
        $this->get($resource::getUrl('create'))->assertOk();
        $this->get($resource::getUrl('edit', ['record' => $record]))->assertOk();
    }

    public function test_the_article_list_shows_real_records(): void
    {
        $this->actingAs($this->admin());
        Post::factory()->create(['title' => 'Judul Yang Dicari', 'category' => 'latest-update']);

        $this->get(PostResource::getUrl('index'))
            ->assertOk()
            ->assertSee('Judul Yang Dicari')
            // Kategori ditampilkan dengan label manusia, bukan slug mentah.
            ->assertSee('Update Terbaru');
    }

    /**
     * Kategori CSR dan SHE sejak awal ada di dropdown, tapi tidak punya pintu
     * sendiri di menu, sehingga terlihat seperti tidak ada sama sekali.
     */
    public function test_every_category_has_its_own_menu_entry(): void
    {
        $this->actingAs($this->admin());

        $items = collect(PostResource::getNavigationItems());

        $this->assertCount(count(config('site.listings')), $items);

        foreach (config('site.listings') as $category => $listing) {
            $item = $items->first(fn ($i) => $i->getLabel() === __($listing['label']));
            $this->assertNotNull($item, "Menu untuk {$category} tidak ada.");
            $this->assertStringContainsString("category={$category}", $item->getUrl());
        }

        // Grup menu mengikuti bentuk navigasi situs publik.
        $this->assertSame('CSR', PostResource::navigationGroupFor('csr'));
        $this->assertSame('SHE', PostResource::navigationGroupFor('she'));
        $this->assertSame('Publikasi', PostResource::navigationGroupFor('publication'));

        // Dan benar-benar tampil di sidebar, bukan hanya ada di array.
        $sidebar = $this->get('/admin')->assertOk();
        foreach (config('site.listings') as $listing) {
            $sidebar->assertSee(__($listing['label']));
        }
        $sidebar->assertSee('CSR')->assertSee('SHE');
    }

    public function test_a_section_menu_only_lists_its_own_articles(): void
    {
        $this->actingAs($this->admin());
        Post::factory()->create(['title' => 'Artikel CSR', 'category' => 'csr-activity']);
        Post::factory()->create(['title' => 'Artikel Berita', 'category' => 'news']);

        $this->get(PostResource::getUrl('index', ['category' => 'csr-activity']))
            ->assertOk()
            ->assertSee('Artikel CSR')
            ->assertDontSee('Artikel Berita');
    }

    public function test_creating_from_a_section_preselects_its_category(): void
    {
        $this->actingAs($this->admin());

        $this->get(PostResource::getUrl('create', ['category' => 'she-activity']))
            ->assertOk()
            ->assertSee('Aktivitas SHE');
    }

    public function test_category_options_come_from_the_site_config(): void
    {
        $this->actingAs($this->admin());

        $response = $this->get(PostResource::getUrl('create'))->assertOk();

        foreach (config('site.listings') as $listing) {
            $response->assertSee(__($listing['label']));
        }
    }
}
