@php
    $monoHeading = 'm-0 mb-3.5 font-meta text-[10.5px] font-normal uppercase tracking-[.08em] text-dim';
    $emails = [
        'Bookings, Remix and Sync Requests' => 'glenn@1namm.com',
        'Licensing/Booking' => 'info@1namm.com',
        'Demo' => 'demo@1namm.com',
        'Web Development' => 'contact@clickstudios-digital.com',
    ];
    $socials = [
        'Twitter' => 'https://x.com/G_n_S_',
        'Facebook' => 'https://www.facebook.com/SnowNStuff',
        'Instagram' => 'https://www.instagram.com/snow_n_stuff',
        'LinkedIn' => 'https://www.linkedin.com/in/glenn-forrestgate-457228a9',
    ];
@endphp

<x-layouts.site :seo="$seo">
    <section class="site-wrap pt-[clamp(32px,5vw,64px)]">
        <x-breadcrumbs :trail="$trail" class="mb-6" />

        <x-section-heading as="h1" title="About">
            <p class="m-0 font-meta text-[11px] uppercase tracking-[.08em] text-mist">Est. 2020 · Stockholm &amp; Romania</p>
        </x-section-heading>

        <div class="grid items-start gap-[clamp(28px,5vw,72px)] md:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)]">
            <div class="flex max-w-[60ch] flex-col gap-[18px]">
                <h2 class="m-0 text-[clamp(1.3rem,2.2vw,1.65rem)] font-medium leading-[1.3]">Management, Label and Music Production</h2>

                <p class="m-0 text-mist">
                    Snow 'n' Stuff is releasing Tech House, Deep House, House and Techno.
                    Management for: THK, G&amp;S, Snow 'n' Stuff and Style Da Kid among others.
                    Tastemaker &amp; Curator of several Spotify playlists. Deep House &amp; Ibiza and many more playlists.
                </p>
            </div>

            <ul class="m-0 list-none border-t border-rule p-0">
                <li class="border-b border-rule py-[18px]">
                    Snow 'n' Stuff stands as a contemporary music label fully attuned to the current era. Its members possess profound proficiency spanning the entire spectrum of musical domains. With over two and a half decades dedicated to the art of Artists and Repertoire (A&amp;R), coupled with substantial involvement in music production, mixing, mastering, and sound design, their collective experience is nothing short of remarkable.
                </li>
                <li class="border-b border-rule py-[18px]">
                    Garnering Grammy nominations, facilitating music licensing on a global scale, securing sync placements across international networks, and amassing millions of radio airplays, Snow 'n' Stuff has indisputably achieved a comprehensive array of accomplishments in the music industry.
                </li>
                <li class="border-b border-rule py-[18px]">
                    Snow 'n' Stuff is actively creating immersive live events and experiences, leveraging their deep roots in the electronic music community and their ability to secure sync placements and airplay. These events feature performances by their roster of artists, as well as collaborations with other labels, promoters, and venues.
                </li>
            </ul>
        </div>
    </section>

    <section id="gallery" class="site-wrap scroll-mt-24 pt-[clamp(64px,9vw,120px)]">
        <x-section-heading title="Photos" subtitle="Some photos of Our Artists" />

        <livewire:photo-gallery :variant="\App\Livewire\PhotoGallery::VARIANT_SITE" />
    </section>

    <section id="contact" class="site-wrap scroll-mt-24 pt-[clamp(64px,9vw,120px)]">
        <x-section-heading title="Contact Us" />

        <div class="grid gap-x-7 gap-y-10 md:grid-cols-[minmax(0,.8fr)_minmax(0,1.2fr)_minmax(0,1.2fr)]">
            <div>
                <h3 class="{{ $monoHeading }}">Location</h3>
                <p class="m-0 text-[15px] text-frost">Stockholm &amp; Romania</p>
            </div>

            <div>
                <h3 class="{{ $monoHeading }}">Email</h3>
                <dl class="m-0 flex flex-col gap-3.5">
                    @foreach ($emails as $purpose => $email)
                        <div>
                            <dt class="text-[13px] text-dim">{{ $purpose }}:</dt>
                            <dd class="m-0"><a href="mailto:{{ $email }}" class="text-[15px] text-frost underline-offset-4 hover:underline">{{ $email }}</a></dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            <div>
                <h3 class="{{ $monoHeading }}">Connect With Us</h3>
                <ul class="m-0 flex list-none flex-col border-t border-rule p-0">
                    @foreach ($socials as $network => $url)
                        <li>
                            <a href="{{ $url }}" target="_blank" rel="noopener" class="flex items-baseline justify-between gap-3 border-b border-rule py-4 font-display text-[clamp(1.6rem,2.6vw,2.2rem)] font-extrabold uppercase leading-none transition-colors hover:text-white">
                                {{ $network }}
                                <span aria-hidden="true" class="font-body text-base font-normal text-dim">↗</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-4 text-mist">Follow us on social media to stay updated with our latest releases, events, and artist news.</p>
            </div>
        </div>
    </section>
</x-layouts.site>
