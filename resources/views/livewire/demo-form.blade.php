@php
    $monoClass = 'm-0 font-meta text-[10.5px] uppercase tracking-[.08em]';
    $labelClass = 'font-meta text-[10px] uppercase tracking-[.08em] text-mist';
    $inputClass = 'w-full rounded border border-rule2 bg-ink px-3.5 py-[13px] text-[15px] leading-[1.3] text-frost transition-colors placeholder:text-dim hover:border-mist focus:border-frost aria-[invalid=true]:border-signal';
    $errorClass = 'm-0 text-[13px] text-[#FF8A8C]';
    $linkClass = 'text-frost underline decoration-rule2 underline-offset-4 transition-colors hover:decoration-frost';
@endphp

<div class="site-wrap pt-[clamp(32px,5vw,64px)]">
    <x-breadcrumbs :trail="$trail" class="mb-6" />

    <x-section-heading as="h1" title="Demos" subtitle="Unreleased Tech House, Deep House, House and Techno." />

    <div class="grid grid-cols-1 items-start gap-[clamp(36px,5vw,72px)] md:grid-cols-[minmax(0,.8fr)_minmax(0,1.2fr)]">
        <div class="flex max-w-[52ch] flex-col gap-8">
            <div class="flex flex-col gap-3.5">
                <h2 class="{{ $monoClass }} font-normal text-dim">What to send</h2>
                <ul class="m-0 flex list-none flex-col border-t border-rule p-0">
                    <li class="border-b border-rule py-3.5">One private streaming link from SoundCloud, Dropbox or Google Drive, sent with the form on this page. No attachments.</li>
                    <li class="border-b border-rule py-3.5">Music you own or control, with every sample cleared.</li>
                    <li class="border-b border-rule py-3.5">A few words about the track and any DJ support so far help.</li>
                </ul>
            </div>

            <div class="flex flex-col gap-3.5">
                <h2 class="{{ $monoClass }} font-normal text-dim">What happens next</h2>
                <p class="m-0 text-[clamp(1.15rem,1.8vw,1.4rem)] font-medium leading-[1.35]">We listen to every demo. If we want to take it further, we'll get in touch.</p>
                <p class="m-0 text-[15px] text-mist">Demos we do not sign are deleted after 12 months. Read how we handle your details in <a href="{{ route('privacy') }}" wire:navigate class="{{ $linkClass }}">Privacy &amp; cookies</a>.</p>
            </div>
        </div>

        <div class="min-w-0 border-t border-rule pt-6 md:pt-7">
            @if ($isSent)
                <div
                    wire:key="demo-sent"
                    role="status"
                    tabindex="-1"
                    x-init="$el.focus({ preventScroll: true })"
                    class="flex flex-col items-start gap-4 outline-none"
                >
                    <p class="{{ $monoClass }} text-mist">Demo received</p>
                    <p class="m-0 font-display text-[clamp(2.2rem,4.4vw,3.4rem)] font-extrabold uppercase leading-[.88]">Thanks, it's in</p>
                    <p class="m-0 max-w-[52ch] text-mist">We listen to every demo. If we want to take it further, we'll get in touch.</p>
                    <button
                        type="button"
                        wire:click="sendAnother"
                        class="mt-1 rounded-full border border-rule2 px-[22px] py-3.5 text-sm font-semibold leading-none text-frost transition-colors hover:border-frost"
                    >Send another demo</button>
                </div>
            @else
                <form wire:key="demo-form" wire:submit="submit" novalidate class="grid gap-x-3.5 gap-y-[18px] sm:grid-cols-2">
                    <p class="m-0 text-[13px] text-mist sm:col-span-2">Fields marked <span aria-hidden="true">*</span><span class="sr-only">with an asterisk</span> are required.</p>

                    <div class="flex min-w-0 flex-col gap-[7px]">
                        <label for="demo-artist" class="{{ $labelClass }}">Artist or project <span aria-hidden="true">*</span></label>
                        <input
                            id="demo-artist"
                            type="text"
                            wire:model="artistName"
                            required
                            maxlength="120"
                            autocomplete="nickname"
                            @error('artistName') aria-invalid="true" aria-describedby="demo-artist-error" @enderror
                            class="{{ $inputClass }}"
                        >
                        @error('artistName') <p id="demo-artist-error" class="{{ $errorClass }}">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex min-w-0 flex-col gap-[7px]">
                        <label for="demo-email" class="{{ $labelClass }}">Email <span aria-hidden="true">*</span></label>
                        <input
                            id="demo-email"
                            type="email"
                            wire:model="email"
                            required
                            maxlength="254"
                            autocomplete="email"
                            placeholder="you@example.com"
                            @error('email') aria-invalid="true" aria-describedby="demo-email-error" @enderror
                            class="{{ $inputClass }}"
                        >
                        @error('email') <p id="demo-email-error" class="{{ $errorClass }}">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex min-w-0 flex-col gap-[7px] sm:col-span-2">
                        <label for="demo-link" class="{{ $labelClass }}">Private link <span aria-hidden="true">*</span></label>
                        <input
                            id="demo-link"
                            type="url"
                            inputmode="url"
                            wire:model="link"
                            required
                            maxlength="2048"
                            placeholder="https://soundcloud.com/…"
                            aria-describedby="demo-link-hint @error('link') demo-link-error @enderror"
                            @error('link') aria-invalid="true" @enderror
                            class="{{ $inputClass }}"
                        >
                        <p id="demo-link-hint" class="m-0 text-[13px] text-dim">SoundCloud, Dropbox or Google Drive, starting with https://.</p>
                        @error('link') <p id="demo-link-error" class="{{ $errorClass }}">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex min-w-0 flex-col gap-[7px]">
                        <label for="demo-genre" class="{{ $labelClass }}">Genre</label>
                        <span class="relative">
                            <select
                                id="demo-genre"
                                wire:model="genre"
                                @error('genre') aria-invalid="true" aria-describedby="demo-genre-error" @enderror
                                class="{{ $inputClass }} cursor-pointer appearance-none pr-10"
                            >
                                <option value="">Choose a genre</option>
                                @foreach ($genres as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <svg class="pointer-events-none absolute right-4 top-1/2 h-3 w-3 -translate-y-1/2 text-mist" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M2.5 4.5L6 8l3.5-3.5" /></svg>
                        </span>
                        @error('genre') <p id="demo-genre-error" class="{{ $errorClass }}">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex min-w-0 flex-col gap-[7px]">
                        <label for="demo-country" class="{{ $labelClass }}">Country</label>
                        <input
                            id="demo-country"
                            type="text"
                            wire:model="country"
                            maxlength="100"
                            autocomplete="country-name"
                            @error('country') aria-invalid="true" aria-describedby="demo-country-error" @enderror
                            class="{{ $inputClass }}"
                        >
                        @error('country') <p id="demo-country-error" class="{{ $errorClass }}">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex min-w-0 flex-col gap-[7px] sm:col-span-2">
                        <label for="demo-message" class="{{ $labelClass }}">Message</label>
                        <textarea
                            id="demo-message"
                            wire:model="message"
                            rows="5"
                            maxlength="5000"
                            placeholder="Tell us about the track and any DJ support so far"
                            @error('message') aria-invalid="true" aria-describedby="demo-message-error" @enderror
                            class="{{ $inputClass }} min-h-[120px] resize-y"
                        ></textarea>
                        @error('message') <p id="demo-message-error" class="{{ $errorClass }}">{{ $message }}</p> @enderror
                    </div>

                    {{-- The trap: kept out of sight and out of the tab order; only bots fill it in. --}}
                    <div aria-hidden="true" class="absolute -left-[9999px] top-auto h-px w-px overflow-hidden">
                        <label for="demo-website">Website</label>
                        <input id="demo-website" type="text" wire:model="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="flex flex-col gap-[7px] sm:col-span-2">
                        <label for="demo-rights" class="flex items-start gap-2.5 text-sm leading-[1.45] text-mist">
                            <input
                                id="demo-rights"
                                type="checkbox"
                                wire:model="rightsConfirmed"
                                required
                                @error('rightsConfirmed') aria-invalid="true" aria-describedby="demo-rights-error" @enderror
                                class="mt-0.5 h-4 w-4 flex-none cursor-pointer accent-frost"
                            >
                            <span>I own or control all rights to this music and any samples are cleared. <span aria-hidden="true">*</span></span>
                        </label>
                        @error('rightsConfirmed') <p id="demo-rights-error" class="{{ $errorClass }}">{{ $message }}</p> @enderror
                    </div>

                    <p class="m-0 text-[13px] text-dim sm:col-span-2">We use your details only to review your demo and to reply. See <a href="{{ route('privacy') }}" wire:navigate class="{{ $linkClass }}">Privacy &amp; cookies</a>.</p>

                    @error('form')
                        <p role="alert" class="m-0 rounded border border-signal/60 bg-signal/10 px-3.5 py-3 text-sm text-frost sm:col-span-2">{{ $message }}</p>
                    @enderror

                    <div class="sm:col-span-2">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="submit"
                            class="inline-flex items-center justify-center gap-2.5 rounded-full border border-frost bg-frost px-[22px] py-3.5 text-sm font-semibold leading-none tracking-[.01em] text-ink transition-colors hover:bg-white disabled:cursor-wait disabled:opacity-70"
                        >
                            <span wire:loading.remove wire:target="submit">Send demo</span>
                            <span wire:loading wire:target="submit">Sending…</span>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
