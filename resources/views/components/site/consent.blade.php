{{--
    The cookie banner. The choice lives in the visitor's browser, so the
    banner is always rendered hidden and resources/js/consent.js shows it
    until a choice is made (and again after six months). A non-modal dialog
    fixed to the bottom of the screen, above the footer player when it is
    open: the page behind it never moves.

    First level: Accept all, Reject all and Customize, alike. Customize, and
    the "Cookie settings" buttons ([data-consent-open]) at any time, open the
    second level with one switch per category. Nothing is ticked in advance.
--}}
@php
    $buttonClass = 'min-h-11 rounded-full border border-rule2 bg-ink px-3 py-2.5 text-[13.5px] font-semibold leading-tight text-frost transition-colors hover:border-frost';
    $linkClass = 'self-start text-[13px] text-mist underline decoration-rule2 underline-offset-4 transition-colors hover:text-frost hover:decoration-frost';
    $categories = [
        [
            'key' => 'necessary',
            'label' => 'Necessary',
            'text' => 'Keep the site working: security for forms and this choice. Always on.',
        ],
        [
            'key' => 'analytics',
            'label' => 'Analytics',
            'text' => 'Google Analytics counts visits and the pages viewed, so we know what people listen to. Sets the _ga cookies.',
        ],
        [
            'key' => 'media',
            'label' => 'External media',
            'text' => 'Players from Spotify, Beatport and nfan.link. They load from those services, which may set their own cookies.',
        ],
    ];
@endphp

<section
    id="cookie-consent"
    data-consent-banner
    data-markdown-ignore
    role="dialog"
    aria-modal="false"
    aria-labelledby="consent-title"
    aria-describedby="consent-text"
    hidden
    class="fixed inset-x-4 bottom-[calc(var(--sns-player-space,0px)+16px+env(safe-area-inset-bottom,0px))] z-[55] sm:left-6 sm:right-auto sm:w-[460px]"
>
    <div class="max-h-[calc(100dvh-32px)] overflow-y-auto rounded-2xl border border-rule2 bg-slab/[.97] p-5 text-frost shadow-[0_24px_60px_rgb(0_0_0/.55)] backdrop-blur-[18px] sm:p-6">
        <div data-consent-view="notice">
            <div class="flex flex-col gap-4">
                <h2 id="consent-title" tabindex="-1" class="m-0 font-display text-[28px] font-extrabold uppercase leading-[.9] outline-none">Cookies &amp; players</h2>

                <p id="consent-text" class="m-0 text-[14.5px] leading-[1.5] text-mist">We would like to count visits with Google Analytics and to show players from Spotify, Beatport and nfan.link, which set their own cookies. Nothing loads until you choose, and you can change your mind at any time in Cookie settings.</p>

                <div class="grid grid-cols-3 gap-2">
                    <button type="button" data-consent-action="accept" class="{{ $buttonClass }}">Accept all</button>
                    <button type="button" data-consent-action="reject" class="{{ $buttonClass }}">Reject all</button>
                    <button type="button" data-consent-action="customize" aria-controls="consent-settings" class="{{ $buttonClass }}">Customize</button>
                </div>

                <a href="{{ route('privacy') }}" wire:navigate class="{{ $linkClass }}">Privacy &amp; cookies</a>
            </div>
        </div>

        <div id="consent-settings" data-consent-view="settings" hidden>
            <div class="flex flex-col gap-4">
                <div class="flex items-start justify-between gap-4">
                    <h2 id="consent-settings-title" tabindex="-1" class="m-0 font-display text-[28px] font-extrabold uppercase leading-[.9] outline-none">Cookie settings</h2>

                    <button
                        type="button"
                        data-consent-action="close"
                        hidden
                        class="-mr-1 -mt-1 h-10 w-10 flex-none place-items-center rounded-full border border-rule2 text-frost transition-colors hover:border-frost [&:not([hidden])]:grid"
                    >
                        <span class="sr-only">Close cookie settings</span>
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" /></svg>
                    </button>
                </div>

                <p class="m-0 text-[14.5px] leading-[1.5] text-mist">Choose what may load on this site. Your choice is kept in this browser for six months.</p>

                <ul class="m-0 list-none p-0">
                    @foreach ($categories as $category)
                        <li class="flex items-start justify-between gap-4 border-t border-rule py-3.5">
                            <div class="min-w-0">
                                <label for="consent-{{ $category['key'] }}" class="font-semibold">{{ $category['label'] }}</label>
                                <p id="consent-{{ $category['key'] }}-text" class="m-0 mt-1 text-[13.5px] leading-[1.45] text-mist">{{ $category['text'] }}</p>
                            </div>

                            <span class="relative mt-0.5 inline-flex h-6 w-11 flex-none">
                                <input
                                    type="checkbox"
                                    role="switch"
                                    id="consent-{{ $category['key'] }}"
                                    aria-describedby="consent-{{ $category['key'] }}-text"
                                    @if ($category['key'] === 'necessary')
                                        checked
                                        disabled
                                    @else
                                        data-consent-choice="{{ $category['key'] }}"
                                    @endif
                                    class="peer absolute inset-0 m-0 h-full w-full cursor-pointer appearance-none rounded-full border border-rule2 bg-ink transition-colors checked:border-frost checked:bg-frost disabled:cursor-default disabled:opacity-60"
                                >
                                <span aria-hidden="true" class="pointer-events-none absolute left-[3px] top-[3px] h-[18px] w-[18px] rounded-full bg-mist transition-transform peer-checked:translate-x-5 peer-checked:bg-ink"></span>
                            </span>
                        </li>
                    @endforeach
                </ul>

                <div class="grid grid-cols-3 gap-2">
                    <button type="button" data-consent-action="save" class="{{ $buttonClass }}">Save choices</button>
                    <button type="button" data-consent-action="accept" class="{{ $buttonClass }}">Accept all</button>
                    <button type="button" data-consent-action="reject" class="{{ $buttonClass }}">Reject all</button>
                </div>

                <a href="{{ route('privacy') }}" wire:navigate class="{{ $linkClass }}">Privacy &amp; cookies</a>
            </div>
        </div>
    </div>
</section>
