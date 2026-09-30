<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pemisahan peran.
 *
 * Isi pesan dari pengunjung adalah data pribadi. Sebelum ini setiap akun yang
 * bisa masuk panel dapat membacanya, termasuk akun yang tugasnya hanya
 * menyunting artikel.
 */
class UserRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_accounts_became_super_admins_not_contributors(): void
    {
        // Migrasi menaikkan akun lama, tidak menurunkannya. Menurunkan hak akun
        // yang sedang dipakai akan mengunci orang di luar panelnya sendiri
        // tepat saat sistem peran diperkenalkan.
        $this->assertSame(UserRole::SuperAdmin, User::factory()->superAdmin()->create()->role);
    }

    public function test_a_contributor_cannot_open_contact_messages(): void
    {
        $this->actingAs(User::factory()->contributor()->create());

        $this->assertFalse(ContactMessageResource::canAccess());
        $this->assertFalse(UserResource::canAccess());
        // Tapi tetap bisa mengerjakan tugasnya.
        $this->assertTrue(PostResource::canAccess());
    }

    public function test_a_reviewer_cannot_read_contact_messages_either(): void
    {
        // Peninjau membaca naskah, bukan korespondensi pengunjung. Haknya naik
        // pada persetujuan, bukan pada data pribadi.
        $this->actingAs(User::factory()->reviewer()->create());

        $this->assertFalse(ContactMessageResource::canAccess());
        $this->assertFalse(UserResource::canAccess());
        $this->assertTrue(PostResource::canAccess());
    }

    public function test_a_corporate_admin_reads_messages_but_cannot_manage_accounts(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->assertTrue(ContactMessageResource::canAccess());
        $this->assertFalse(UserResource::canAccess());
    }

    public function test_a_super_admin_can_open_everything(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        $this->assertTrue(ContactMessageResource::canAccess());
        $this->assertTrue(UserResource::canAccess());
        $this->assertTrue(PostResource::canAccess());
    }

    /**
     * Menyembunyikan menu saja tidak cukup: URL-nya tetap bisa ditebak, dan
     * pesan dari pengunjung tidak boleh terbuka karena seseorang mengetik
     * alamatnya langsung.
     */
    public function test_the_url_is_closed_too_not_only_the_menu(): void
    {
        ContactMessage::factory()->create(['subject' => 'Rahasia Pengunjung']);

        $this->actingAs(User::factory()->contributor()->create());
        $this->get(ContactMessageResource::getUrl('index'))->assertForbidden();

        $this->actingAs(User::factory()->superAdmin()->create());
        $this->get(ContactMessageResource::getUrl('index'))
            ->assertOk()
            ->assertSee('Rahasia Pengunjung');
    }
}
