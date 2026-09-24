@props(['unit' => null])

{{-- Tidak menampilkan apa pun selama unitnya belum punya situs: tombol mati
     yang mengarah ke mana-mana lebih buruk daripada tidak ada tombol. --}}
@if ($unit?->website_url)
  <div class="reveal mt-10 rounded-2xl border border-slate-200 bg-slate-50 p-6 sm:flex sm:items-center sm:justify-between sm:gap-6">
    <div>
      <p class="font-bold text-slate-800">Situs resmi {{ $unit->name }}</p>
      <p class="mt-1 text-sm text-slate-500">Informasi lengkap unit bisnis ini ada di situsnya sendiri.</p>
    </div>
    <a href="{{ $unit->website_url }}" target="_blank" rel="noopener"
       class="mt-4 inline-flex shrink-0 items-center gap-2 rounded-full bg-primary px-6 py-3 text-sm font-semibold text-white transition hover:bg-primary-dark sm:mt-0">
      Kunjungi Situs
      <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H18m0 0v4.5M18 6l-7.5 7.5M9 6H7a3 3 0 00-3 3v8a3 3 0 003 3h8a3 3 0 003-3v-2"/>
      </svg>
    </a>
  </div>
@endif
