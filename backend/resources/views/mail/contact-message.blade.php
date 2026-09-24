<x-mail::message>
# Pesan baru dari formulir situs

**Nama:** {{ $contactMessage->name }}
**Surel:** {{ $contactMessage->email }}
@if ($contactMessage->phone)
**Telepon:** {{ $contactMessage->phone }}
@endif
**Perihal:** {{ $contactMessage->subject }}
**Bahasa pengirim:** {{ strtoupper($contactMessage->locale) }}

---

{{ $contactMessage->message }}

---

Balas surel ini untuk menjawab langsung ke pengirimnya.

<x-mail::button :url="url('/admin/contact-messages')">
Lihat di panel admin
</x-mail::button>
</x-mail::message>
