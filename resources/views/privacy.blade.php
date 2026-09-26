{{--
    The privacy policy (art. 13 GDPR) and the list of cookies.

    DRAFT, written in English for the redesign on 26.09.2026: the client and
    their legal counsel must review it before launch. Until the site runs in
    production the page says so. The controller's details come from
    config/site.php (PRIVACY_* in .env); PrivacyController::UPDATED_AT dates
    this version.
--}}
@php
    $h2Class = 'm-0 font-display text-[clamp(1.9rem,3.4vw,2.6rem)] font-extrabold uppercase leading-[.95]';
    $h3Class = 'm-0 text-[1.1rem] font-semibold leading-snug';
    $textClass = 'm-0 text-mist';
    $linkClass = 'text-frost underline decoration-rule2 underline-offset-4 transition-colors hover:decoration-frost';
    $sectionClass = 'flex scroll-mt-24 flex-col gap-4 border-t border-rule pt-6';
    $listClass = 'm-0 flex list-disc flex-col gap-2 pl-5 text-mist marker:text-dim';
    $buttonClass = 'self-start rounded-full border border-rule2 px-[22px] py-3.5 text-sm font-semibold leading-none text-frost transition-colors hover:border-frost';
    $email = $controller['contact_email'];
    $contents = [
        'controller' => 'Who is responsible',
        'data' => 'What we collect and why',
        'recipients' => 'Who receives it',
        'transfers' => 'Transfers outside the EU',
        'retention' => 'How long we keep it',
        'rights' => 'Your rights',
        'complaints' => 'Complaints',
        'cookies' => 'Cookies',
        'changes' => 'Changes to this policy',
    ];
    $cookies = [
        ['name' => $sessionCookie, 'provider' => 'This site', 'purpose' => 'Keeps your session, e.g. while you fill in the demo form.', 'duration' => $sessionDuration, 'category' => 'Necessary'],
        ['name' => 'XSRF-TOKEN', 'provider' => 'This site', 'purpose' => 'Protects forms against forged submissions.', 'duration' => $sessionDuration, 'category' => 'Necessary'],
        ['name' => 'sns.consent (local storage)', 'provider' => 'This site', 'purpose' => 'Remembers your cookie choice.', 'duration' => '6 months', 'category' => 'Necessary'],
        ['name' => '_ga', 'provider' => 'Google Analytics', 'purpose' => 'Distinguishes visitors.', 'duration' => '2 years', 'category' => 'Analytics'],
        ['name' => '_ga_'.$analyticsContainerId, 'provider' => 'Google Analytics', 'purpose' => 'Keeps the state of a visit.', 'duration' => '2 years', 'category' => 'Analytics'],
        ['name' => 'sp_t', 'provider' => 'Spotify', 'purpose' => 'Recognises your browser in the Spotify player.', 'duration' => '1 year', 'category' => 'External media'],
        ['name' => 'sp_landing', 'provider' => 'Spotify', 'purpose' => 'Records how the Spotify player was reached.', 'duration' => '1 day', 'category' => 'External media'],
        ['name' => 'Beatport cookies', 'provider' => 'Beatport', 'purpose' => "Set by the Beatport player; see Beatport's cookie policy.", 'duration' => 'Set by Beatport', 'category' => 'External media'],
        ['name' => 'nfan.link cookies', 'provider' => 'nfan.link', 'purpose' => 'Set by the nfan.link player, if any.', 'duration' => 'Set by nfan.link', 'category' => 'External media'],
    ];
@endphp

<x-layouts.site :seo="$seo">
    <div class="site-wrap pt-[clamp(32px,5vw,64px)]">
        <x-breadcrumbs :trail="$trail" class="mb-6" />

        <x-section-heading as="h1" title="Privacy & cookies">
            <p class="m-0 font-meta text-[11px] uppercase tracking-[.08em] text-mist">Version of <time datetime="{{ $updatedAt->toDateString() }}">{{ $updatedAt->format('j F Y') }}</time></p>
        </x-section-heading>

        {{-- Shown until launch: remove once the client and legal counsel have approved the text. --}}
        @unless (app()->isProduction())
            <p role="note" class="mb-10 max-w-[72ch] rounded border border-signal/60 bg-signal/10 px-4 py-3 text-sm font-semibold text-frost">Draft — to be reviewed by the client/legal counsel before launch.</p>
        @endunless

        <div class="grid grid-cols-1 items-start gap-[clamp(28px,5vw,72px)] lg:grid-cols-[220px_minmax(0,1fr)]">
            <nav aria-label="On this page" data-markdown-ignore class="lg:sticky lg:top-24">
                <p class="m-0 mb-3.5 font-meta text-[10.5px] uppercase tracking-[.08em] text-dim">On this page</p>
                <ol class="m-0 flex list-none flex-col gap-2 p-0 text-[15px]">
                    @foreach ($contents as $id => $title)
                        <li><a href="#{{ $id }}" class="text-mist transition-colors hover:text-frost">{{ $title }}</a></li>
                    @endforeach
                </ol>
            </nav>

            <div class="flex min-w-0 max-w-[72ch] flex-col gap-12 text-[16.5px] leading-[1.65]">
                <p class="m-0 text-[clamp(1.15rem,1.8vw,1.4rem)] font-medium leading-[1.4]">This page explains what personal data this website collects, why, how long we keep it and what your rights are. In short: analytics and music players from other services load only if you agree, and the demos you send are used only to review your music.</p>

                <section id="controller" class="{{ $sectionClass }}">
                    <h2 class="{{ $h2Class }}">Who is responsible</h2>
                    <p class="{{ $textClass }}">The controller of your personal data on this website is:</p>
                    <dl class="m-0 grid gap-x-6 gap-y-2 sm:grid-cols-[max-content_1fr]">
                        <div class="contents">
                            <dt class="text-dim">Controller</dt>
                            <dd class="m-0">{{ $controller['controller_name'] }}</dd>
                        </div>
                        @if (filled($controller['controller_address']))
                            <div class="contents">
                                <dt class="text-dim">Address</dt>
                                <dd class="m-0">{{ $controller['controller_address'] }}</dd>
                            </div>
                        @endif
                        <div class="contents">
                            <dt class="text-dim">Email</dt>
                            <dd class="m-0"><a href="mailto:{{ $email }}" class="{{ $linkClass }}">{{ $email }}</a></dd>
                        </div>
                    </dl>
                    <p class="{{ $textClass }}">For anything about your personal data, write to that address.</p>
                </section>

                <section id="data" class="{{ $sectionClass }}">
                    <h2 class="{{ $h2Class }}">What we collect and why</h2>

                    <h3 class="{{ $h3Class }}">Visiting the site</h3>
                    <p class="{{ $textClass }}">When you open a page, our hosting provider's servers record technical data: your IP address, the date and time, the page requested, the page you came from and your browser's user agent. We use these logs to deliver the site, keep it secure and look into errors or abuse. Legal basis: our legitimate interest in running a secure website (Art. 6(1)(f) GDPR).</p>
                    <p class="{{ $textClass }}">The site also sets two strictly necessary cookies, for your session and against forged form submissions, and remembers your cookie choice in your browser. They need no consent and are listed under <a href="#cookies" class="{{ $linkClass }}">Cookies</a>.</p>

                    <h3 class="{{ $h3Class }}">Fonts and cover images</h3>
                    <p class="{{ $textClass }}">The site's fonts come from Bunny Fonts (fonts.bunny.net), run by BunnyWay d.o.o. in Slovenia, which says it does not log visitors' IP addresses. Until we upload a release's or playlist's own cover, the artwork is shown from Spotify's image servers (i.scdn.co and image-cdn-*.spotifycdn.com). To deliver these files, both services receive your IP address and browser data, as any web server does; no player or script is loaded from them. Legal basis: our legitimate interest in showing the site and the artwork (Art. 6(1)(f) GDPR).</p>

                    <h3 class="{{ $h3Class }}">Analytics, only with your consent</h3>
                    <p class="{{ $textClass }}">If you accept analytics cookies, we use Google Analytics 4 to count visits and see which pages are viewed, on which kind of device and roughly where (country or city). Google Analytics sets the _ga cookies and receives your IP address, which Google uses to estimate the location and says it does not store. Without your consent, Google Analytics is not loaded at all. Legal basis: your consent (Art. 6(1)(a) GDPR, and Art. 5(3) of the ePrivacy Directive as applied by national law).</p>

                    <h3 class="{{ $h3Class }}">External media, only with your consent or on your click</h3>
                    <p class="{{ $textClass }}">The music players on this site come from Spotify (the player at the bottom of the screen and the players in news posts), Beatport and nfan.link. A player loads only after you allow external media, choose Continue in the player or press Load player on one of them. Once it loads, the provider receives your IP address and browser data and may set its own cookies; it handles that data as an independent controller, under its own privacy policy. Legal basis: your consent (Art. 6(1)(a) GDPR).</p>

                    <h3 class="{{ $h3Class }}">Demos</h3>
                    <p class="{{ $textClass }}">When you <a href="{{ route('demos') }}" wire:navigate class="{{ $linkClass }}">send a demo</a>, we receive the artist or project name, your email address, the private link and, if you give them, the genre, your country and your message, plus your confirmation that you control the rights. We use them to listen to and assess your music, and to contact you if we want to take it further. To keep out spam and limit the number of submissions, we also keep your IP address and email address, in hashed form, for one hour. Legal basis: steps you ask us to take before a possible contract (Art. 6(1)(b) GDPR) and our legitimate interest in reviewing the music sent to the label and protecting the form (Art. 6(1)(f) GDPR).</p>

                    <h3 class="{{ $h3Class }}">Email</h3>
                    <p class="{{ $textClass }}">When you write to one of the addresses on this site, we use your message and contact details to answer you. Legal basis: our legitimate interest in answering questions about the label (Art. 6(1)(f) GDPR), or steps before a contract when you ask about bookings, licensing or releases (Art. 6(1)(b) GDPR).</p>
                </section>

                <section id="recipients" class="{{ $sectionClass }}">
                    <h2 class="{{ $h2Class }}">Who receives it</h2>
                    <ul class="{{ $listClass }}">
                        <li>Our hosting provider, which stores the website, its database (including the demos) and the server logs for us, as our processor.</li>
                        <li>Our email provider, which delivers the notifications of new demos and stores the label's email, as our processor.</li>
                        <li>Google (Google Ireland Limited and Google LLC), for Google Analytics, only if you accept analytics cookies. Google processes this data for us, as our processor.</li>
                        <li>Spotify (Spotify AB, Sweden), Beatport and nfan.link, once one of their players has loaded, as independent controllers.</li>
                        <li>Bunny Fonts (BunnyWay d.o.o., Slovenia) and Spotify's image servers, which deliver the fonts and the cover images.</li>
                    </ul>
                    <p class="{{ $textClass }}">We do not sell your data or use it for advertising.</p>
                </section>

                <section id="transfers" class="{{ $sectionClass }}">
                    <h2 class="{{ $h2Class }}">Transfers outside the EU</h2>
                    <p class="{{ $textClass }}">Some providers, such as Google, may process data in the United States. For these transfers we rely on the EU-US Data Privacy Framework where the provider is certified under it, and otherwise on the European Commission's standard contractual clauses.</p>
                </section>

                <section id="retention" class="{{ $sectionClass }}">
                    <h2 class="{{ $h2Class }}">How long we keep it</h2>
                    <ul class="{{ $listClass }}">
                        <li>Demos: up to 12 months if we do not sign the music, then deleted automatically. For music we sign, as long as the release and our legal obligations require.</li>
                        <li>Server logs: for the period set by our hosting provider.</li>
                        <li>Your cookie choice: six months, after which we ask again.</li>
                        <li>Google Analytics data: for the retention period set in our Google Analytics account, at most 14 months.</li>
                        <li>Emails: as long as needed to handle your request and any follow-up.</li>
                        <li>The hashed IP and email addresses that limit demo submissions: one hour.</li>
                    </ul>
                </section>

                <section id="rights" class="{{ $sectionClass }}">
                    <h2 class="{{ $h2Class }}">Your rights</h2>
                    <p class="{{ $textClass }}">You have the right to access your personal data, to have it corrected or erased, to restrict its use, to object to processing based on our legitimate interests and to receive the data you gave us in a portable format.</p>
                    <p class="{{ $textClass }}">Where we rely on your consent, you can withdraw it at any time, as easily as you gave it, with the Cookie settings button at the bottom of every page. Withdrawing does not affect what happened before.</p>
                    <p class="{{ $textClass }}">To use your rights, write to <a href="mailto:{{ $email }}" class="{{ $linkClass }}">{{ $email }}</a>. We answer within one month.</p>
                </section>

                <section id="complaints" class="{{ $sectionClass }}">
                    <h2 class="{{ $h2Class }}">Complaints</h2>
                    <p class="{{ $textClass }}">If you think we handle your data unlawfully, you can complain to a data protection authority, in particular in the EU country where you live, work or where the issue happened:</p>
                    <ul class="{{ $listClass }}">
                        <li>in Sweden, the Swedish Authority for Privacy Protection, IMY (<a href="https://www.imy.se/en/" target="_blank" rel="noopener" class="{{ $linkClass }}">imy.se</a>);</li>
                        <li>in Romania, the National Supervisory Authority for Personal Data Processing, ANSPDCP (<a href="https://www.dataprotection.ro/" target="_blank" rel="noopener" class="{{ $linkClass }}">dataprotection.ro</a>);</li>
                        <li>elsewhere, your local authority (<a href="https://www.edpb.europa.eu/about-edpb/about-edpb/members_en" target="_blank" rel="noopener" class="{{ $linkClass }}">list of EU authorities</a>).</li>
                    </ul>
                    <p class="{{ $textClass }}">We would be glad to have the chance to sort out your concern first.</p>
                </section>

                <section id="cookies" class="{{ $sectionClass }}">
                    <h2 class="{{ $h2Class }}">Cookies</h2>
                    <p class="{{ $textClass }}">Necessary cookies and storage are always on. Analytics and external media are used only if you allow them, and you can change your choice at any time.</p>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[640px] border-collapse text-left text-[14.5px] leading-[1.45]">
                            <thead>
                                <tr class="border-b border-rule2 font-meta text-[10px] uppercase tracking-[.08em] text-dim">
                                    <th scope="col" class="py-2.5 pr-4 font-normal">Name</th>
                                    <th scope="col" class="py-2.5 pr-4 font-normal">Provider</th>
                                    <th scope="col" class="py-2.5 pr-4 font-normal">Purpose</th>
                                    <th scope="col" class="py-2.5 pr-4 font-normal">Duration</th>
                                    <th scope="col" class="py-2.5 font-normal">Category</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cookies as $cookie)
                                    <tr class="border-b border-rule align-top">
                                        <th scope="row" class="py-3 pr-4 font-meta text-[12.5px] font-normal text-frost [overflow-wrap:anywhere]">{{ $cookie['name'] }}</th>
                                        <td class="py-3 pr-4">{{ $cookie['provider'] }}</td>
                                        <td class="py-3 pr-4 text-mist">{{ $cookie['purpose'] }}</td>
                                        <td class="py-3 pr-4 text-mist">{{ $cookie['duration'] }}</td>
                                        <td class="py-3">{{ $cookie['category'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <p class="{{ $textClass }}">Cookies from other services are set and controlled by them; we list the ones we know of. When you withdraw your consent to analytics, we delete the _ga cookies. Withdrawing your consent to external media stops the players from loading again, but cookies already set by Spotify, Beatport or nfan.link can only be removed in your browser's settings.</p>

                    <button type="button" data-consent-open aria-controls="cookie-consent" class="{{ $buttonClass }}">Cookie settings</button>
                </section>

                <section id="changes" class="{{ $sectionClass }}">
                    <h2 class="{{ $h2Class }}">Changes to this policy</h2>
                    <p class="{{ $textClass }}">We update this page when the way we use personal data changes, and ask for your consent again when a change affects it. This version is dated <time datetime="{{ $updatedAt->toDateString() }}">{{ $updatedAt->format('j F Y') }}</time>.</p>
                </section>
            </div>
        </div>
    </div>
</x-layouts.site>
