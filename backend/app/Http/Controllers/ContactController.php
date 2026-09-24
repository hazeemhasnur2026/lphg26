<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactMessageRequest;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\Office;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('pages.contact.contact-us', [
            'headOffice' => Office::headquarters()->ordered()->first(),
        ]);
    }

    public function store(ContactMessageRequest $request): RedirectResponse
    {
        $message = ContactMessage::create($request->safe()->except('website') + [
            'locale' => app()->getLocale(),
            'ip_address' => $request->ip(),
        ]);

        /*
         * Pesannya sudah tersimpan sebelum baris ini. Surel yang gagal terkirim
         * karena itu tidak boleh menggagalkan kiriman pengunjung: dia sudah
         * melakukan bagiannya, dan pesannya tidak hilang. Kegagalannya dicatat
         * supaya bisa ditelusuri, bukan disembunyikan.
         */
        try {
            Mail::to(config('mail.contact_recipient'))->send(new ContactMessageReceived($message));
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim surel formulir kontak', [
                'contact_message_id' => $message->id,
                'exception' => $e->getMessage(),
            ]);
        }

        return back()->with('contact.sent', true);
    }
}
