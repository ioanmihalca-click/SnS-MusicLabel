{{--
    Site footer: label pages, contacts, locations and the label's profiles,
    then the full-width wordmark.
--}}
@php
    $labelLinks = [
        'Releases' => route('releases.index'),
        'Artists' => route('artists.index'),
        'Playlists' => route('playlists.index'),
        'News' => route('blog.index'),
        'About' => route('about'),
    ];
    $contacts = [
        'Bookings, remixes, sync' => 'glenn@1namm.com',
        'Licensing' => 'info@1namm.com',
        'Demos' => 'demo@1namm.com',
    ];
    $followLinks = [
        'Instagram' => 'https://www.instagram.com/snow_n_stuff',
        'Facebook' => 'https://www.facebook.com/SnowNStuff',
        'X' => 'https://x.com/G_n_S_',
        'Spotify' => 'https://open.spotify.com/artist/6wIX9hW2uQAVv190xXV9mA',
    ];
    $headingClass = 'mb-3.5 font-meta text-[10.5px] font-normal uppercase tracking-[.08em] text-dim';
    $linkClass = 'text-mist transition-colors hover:text-frost';
@endphp

<footer data-markdown-ignore class="mt-[clamp(64px,9vw,120px)] border-t border-rule pb-10">
    <div class="site-wrap">
        <div class="grid grid-cols-1 gap-7 pb-10 pt-[52px] min-[481px]:grid-cols-2 md:grid-cols-4">
            <div>
                <h2 class="{{ $headingClass }}">Label</h2>
                <ul class="flex flex-col gap-[9px]">
                    @foreach ($labelLinks as $label => $href)
                        <li><a href="{{ $href }}" class="{{ $linkClass }}">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h2 class="{{ $headingClass }}">Contact</h2>
                <ul class="flex flex-col gap-[9px]">
                    @foreach ($contacts as $purpose => $email)
                        <li>
                            <span class="block text-[13px] text-dim">{{ $purpose }}</span>
                            <a href="mailto:{{ $email }}" class="text-[15px] text-frost underline-offset-4 hover:underline">{{ $email }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h2 class="{{ $headingClass }}">Based in</h2>
                <ul class="flex flex-col gap-[9px] text-[15px] text-frost">
                    <li>Stockholm, Sweden</li>
                    <li>Romania</li>
                </ul>
            </div>

            <div>
                <h2 class="{{ $headingClass }}">Follow</h2>
                <ul class="flex flex-col gap-[9px]">
                    @foreach ($followLinks as $label => $href)
                        <li><a href="{{ $href }}" target="_blank" rel="noopener" class="{{ $linkClass }}">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>

        <p aria-hidden="true" class="whitespace-nowrap font-display text-[clamp(3rem,12.6vw,13.5rem)] font-black uppercase leading-[.8] tracking-[-.01em] text-frost">Snow 'n' Stuff</p>

        <div class="mt-7 flex flex-wrap justify-between gap-x-6 gap-y-2.5 border-t border-rule pt-[18px] text-[13px] text-dim">
            <p>&copy; {{ now()->year }} Snow 'n' Stuff. All rights reserved.</p>
            <a href="https://clickstudios-digital.com" target="_blank" rel="noopener" class="transition-colors hover:text-frost">Web application by Click Studios Digital</a>
        </div>
    </div>
</footer>
