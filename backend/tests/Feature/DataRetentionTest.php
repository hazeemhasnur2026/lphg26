<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataRetentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_messages_past_the_retention_period_are_pruned(): void
    {
        config(['site.retensi_pesan_bulan' => 24]);

        ContactMessage::factory()->create(['created_at' => now()->subMonths(30)]);
        $masihBaru = ContactMessage::factory()->create(['created_at' => now()->subMonths(6)]);

        $this->artisan('model:prune', ['--model' => [ContactMessage::class]])->assertSuccessful();

        $this->assertSame(1, ContactMessage::count());
        $this->assertTrue(ContactMessage::first()->is($masihBaru));
    }

    /**
     * Masa simpan 0 berarti pemangkasan dimatikan, bukan pemangkasan tanpa
     * syarat. Salah ketik pada satu setelan tidak boleh menghapus semuanya.
     */
    public function test_a_zero_retention_disables_pruning_instead_of_deleting_everything(): void
    {
        config(['site.retensi_pesan_bulan' => 0]);

        ContactMessage::factory()->count(3)->create(['created_at' => now()->subYears(5)]);

        $this->artisan('model:prune', ['--model' => [ContactMessage::class]])->assertSuccessful();

        $this->assertSame(3, ContactMessage::count());
    }
}
