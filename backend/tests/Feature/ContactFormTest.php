<?php

namespace Tests\Feature;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\Office;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Siti Rahmawati',
            'email' => 'siti@example.com',
            'phone' => '08123456789',
            'subject' => 'Permintaan informasi kerja sama',
            'message' => 'Kami ingin menanyakan peluang kerja sama di bidang logistik.',
        ], $overrides);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        RateLimiter::clear('');
    }

    public function test_the_page_shows_the_form_and_the_head_office(): void
    {
        Office::factory()->headquarters()->create(['name' => 'Kantor Pusat Uji']);

        $this->get(route('contact.show'))
            ->assertOk()
            ->assertSee('Kirim Pesan')
            ->assertSee('Kantor Pusat Uji');
    }

    public function test_a_valid_message_is_stored_and_emailed(): void
    {
        $this->post(route('contact.store'), $this->validPayload())
            ->assertRedirect()
            ->assertSessionHas('contact.sent');

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'siti@example.com',
            'subject' => 'Permintaan informasi kerja sama',
            'handled_at' => null,
        ]);

        Mail::assertSent(ContactMessageReceived::class, function (ContactMessageReceived $mail) {
            // Tujuannya satu alamat resmi, dan membalasnya harus sampai ke
            // pengirim aslinya, bukan ke kotak masuk sendiri.
            return $mail->hasTo(config('mail.contact_recipient'))
                && $mail->hasReplyTo('siti@example.com');
        });
    }

    public function test_the_message_survives_a_failing_mail_server(): void
    {
        // Pengunjung sudah melakukan bagiannya; kegagalan SMTP tidak boleh
        // membuat pesannya hilang atau tampil sebagai kesalahan dia.
        Mail::shouldReceive('to->send')->andThrow(new \RuntimeException('SMTP mati'));

        $this->post(route('contact.store'), $this->validPayload())
            ->assertRedirect()
            ->assertSessionHas('contact.sent');

        $this->assertSame(1, ContactMessage::count());
    }

    /** @return array<string, array{array<string, mixed>, string}> */
    public static function invalidPayloads(): array
    {
        return [
            'tanpa nama' => [['name' => ''], 'name'],
            'surel ngawur' => [['email' => 'bukan-surel'], 'email'],
            'tanpa perihal' => [['subject' => ''], 'subject'],
            'pesan terlalu pendek' => [['message' => 'halo'], 'message'],
        ];
    }

    /** @param  array<string, mixed>  $overrides */
    #[DataProvider('invalidPayloads')]
    public function test_invalid_submissions_are_rejected(array $overrides, string $field): void
    {
        $this->post(route('contact.store'), $this->validPayload($overrides))
            ->assertSessionHasErrors($field);

        $this->assertSame(0, ContactMessage::count());
        Mail::assertNothingSent();
    }

    public function test_the_honeypot_rejects_bots_without_telling_them_why(): void
    {
        $this->post(route('contact.store'), $this->validPayload(['website' => 'https://spam.example']))
            ->assertSessionHasErrors('website');

        $this->assertSame(0, ContactMessage::count());
        Mail::assertNothingSent();
    }

    public function test_submissions_are_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('contact.store'), $this->validPayload(['email' => "orang{$i}@example.com"]));
        }

        $this->post(route('contact.store'), $this->validPayload(['email' => 'keenam@example.com']))
            ->assertStatus(429);

        $this->assertSame(5, ContactMessage::count());
    }

    public function test_the_email_address_is_not_published_anywhere(): void
    {
        // Alasan formulir ini ada: alamat yang terpampang di HTML adalah yang
        // pertama dipanen pengirim spam. Kantor pusat sengaja dibuat lengkap
        // dengan surelnya. Tanpa itu pemeriksaan ini lolos tanpa membuktikan
        // apa pun, karena footer memang tidak menampilkan apa-apa saat kosong.
        Office::factory()->headquarters()->create(['email' => 'info@hasnurgroup.com']);

        foreach ([route('contact.show'), '/', '/page/about-us/overview'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertDontSee('info@hasnurgroup.com')
                ->assertDontSee('mailto:info', false);
        }
    }
}
