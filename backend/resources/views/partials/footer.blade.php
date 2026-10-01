<footer id="contact" class="bg-primary-dark text-white pt-20 pb-8">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-12">

      <!-- Kantor -->
      <div>
        <div class="mb-5">
          <x-img src="/assets/img/logo.png" alt="Hasnur Group" class="h-9 w-auto bg-white rounded-md px-2 py-1.5" sizes="176px" />
        </div>

        @if ($headOffice)
          <p class="text-sm font-bold text-white/90">{{ $headOffice->name }}</p>
          <p class="mt-2 text-sm text-white/70 leading-relaxed">
            {{-- Alamat disimpan per baris di database, jadi <br> ditulis di sini
                 dan penyuntingnya tidak perlu tahu HTML. --}}
            @foreach ($headOffice->addressLines() as $line)
              {{ $line }}@if (! $loop->last)<br>@endif
            @endforeach
          </p>
          <p class="mt-3 text-sm text-white/70">
            @if ($headOffice->phone) {{ __('footer.phone') }} {{ $headOffice->phone }}<br> @endif
            @if ($headOffice->fax) {{ __('footer.fax') }} {{ $headOffice->fax }}<br> @endif
            {{-- Alamat surelnya sengaja tidak dipasang sebagai mailto: alamat
                 yang terpampang di HTML adalah yang pertama dipanen pengirim
                 spam, dan lewat formulir pesannya sekaligus tersimpan. --}}
            @if ($headOffice->email)
              {{ __('footer.email') }} <a href="@lurl('/page/contact/contact-us')" class="underline hover:text-accent">{{ __('footer.via_form') }}</a>
            @endif
          </p>
        @endif
      </div>

      <!-- Kantor perwakilan -->
      <div>
        <p class="text-sm font-bold text-white/90">{{ __('footer.branches') }}</p>
        @foreach ($branchOffices as $office)
          <div class="{{ $loop->first ? 'mt-3' : 'mt-4' }} text-sm text-white/70 leading-relaxed">
            <p class="font-semibold text-white/90">{{ $office->name }}</p>
            <p>{{ $office->address }}</p>
          </div>
        @endforeach
      </div>

      <!-- Recent post -->
      <div>
        <p class="text-sm font-bold text-white/90">{{ __('footer.recent') }}</p>
        <ul class="mt-3 space-y-3 text-sm text-white/70">
          @forelse ($footerPosts as $post)
            <li><a href="{{ $post->url }}" @if ($post->isExternal()) target="_blank" rel="noopener" @endif class="hover:text-accent transition leading-snug block">{{ $post->title }}</a></li>
          @empty
            <li>{{ __('footer.no_posts') }}</li>
          @endforelse
        </ul>
      </div>

      <!-- Social + link terkait -->
      <div>
        <p class="text-sm font-bold text-white/90">{{ __('footer.social') }}</p>
        <div class="mt-3 flex gap-3">
          <a href="{{ config('site.sosial.linkedin') }}" target="_blank" rel="noopener" aria-label="LinkedIn Hasnur Group" class="w-9 h-9 rounded-full bg-white/10 hover:bg-accent hover:text-primary-dark flex items-center justify-center transition">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 110-4.125 2.062 2.062 0 010 4.125zM7.114 20.452H3.558V9h3.556v11.452z"/></svg>
          </a>
          <a href="{{ config('site.sosial.instagram') }}" target="_blank" rel="noopener" aria-label="Instagram Hasnur Group" class="w-9 h-9 rounded-full bg-white/10 hover:bg-accent hover:text-primary-dark flex items-center justify-center transition">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.332.014 7.052.072 2.695.272.273 2.69.073 7.052.014 8.332 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.332 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.668-.072-4.948C23.73 2.7 21.31.273 16.949.073 15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
          </a>
          <a href="{{ config('site.sosial.youtube') }}" target="_blank" rel="noopener" aria-label="YouTube Hasnur Group" class="w-9 h-9 rounded-full bg-white/10 hover:bg-accent hover:text-primary-dark flex items-center justify-center transition">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a2.994 2.994 0 00-2.107-2.117C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.391.524A2.994 2.994 0 00.502 6.186 31.36 31.36 0 000 12a31.36 31.36 0 00.502 5.814 2.994 2.994 0 002.107 2.117c1.886.524 9.391.524 9.391.524s7.505 0 9.391-.524a2.994 2.994 0 002.107-2.117A31.36 31.36 0 0024 12a31.36 31.36 0 00-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
          </a>
        </div>

        <p class="mt-6 text-sm font-bold text-white/90">{{ __('footer.links') }}</p>
        <ul class="mt-3 space-y-2 text-sm text-white/70">
          <li><a href="https://www.hasnurcentre.org" target="_blank" rel="noopener" class="hover:text-accent transition">Hasnur Centre</a></li>
          <li><a href="https://www.baritoputera.co.id" target="_blank" rel="noopener" class="hover:text-accent transition">PS Barito Putera</a></li>
          <li><a href="@lurl('/page/contact/contact-us')" class="hover:text-accent transition">{{ __('nav.contact') }}</a></li>
        </ul>
      </div>
    </div>

    <div class="mt-16 pt-6 border-t border-white/10 text-center text-sm text-white/50">
      <a href="@lurl('/page/legal/kebijakan-privasi')" class="underline hover:text-accent">{{ __('footer.privacy') }}</a>
      <p class="mt-2">{{ __('footer.rights') }}</p>
    </div>
  </div>
</footer>
