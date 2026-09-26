/**
 * The footer player: Spotify's own embed, shown as it is (152px high), in a
 * bar that stays on screen while wire:navigate swaps the page.
 *
 * Why the bar lives outside <body>: Livewire's @persist takes a persisted
 * element out of the page and puts it back on every visit, and an iframe put
 * back reloads, so the music would stop. Livewire only replaces <body>, so
 * the bar is created on the first Play as a child of <html>, after <body>,
 * where no visit ever touches it.
 *
 * - Play buttons (x-play-button) carry data-play (a spotify: URI),
 *   data-play-title, data-play-credit and data-play-url.
 * - The first Play asks, inside the bar, whether to continue: Spotify sets
 *   cookies. Until the visitor continues, nothing is requested from Spotify.
 *   Continuing allows external media (consent.js), so a visitor who already
 *   did, here or in the cookie banner, is not asked; withdrawn in the
 *   Cookie settings, the bar closes.
 * - Spotify's iFrame API is loaded once, on the first accepted Play.
 * - Previous and next walk the Play buttons of the page where playback
 *   started (the queue) and stop at both ends.
 * - Each change is announced as a `sns:player` event ({ uri, isPaused }),
 *   which keeps aria-pressed and data-state of the matching buttons in sync.
 */

import { grantMediaConsent, hasMediaConsent } from './consent';

const SPOTIFY_API_URL = 'https://open.spotify.com/embed/iframe-api/v1';
const SPOTIFY_API_TIMEOUT_MS = 15000;
const EMBED_HEIGHT = 152;
const PLAY_HINT_DELAY_MS = 2500;
const PRIVACY_URL = '/privacy#cookies';
const HOST_ATTRIBUTE = 'data-sns-player';

/**
 * @typedef {object} PlayItem
 * @property {string} uri    e.g. "spotify:album:3zifCl5R2DaZGEmrPNUM1N"
 * @property {string} title
 * @property {string} credit May be empty.
 * @property {string} url    The release's page, or /playlists.
 */

/**
 * @typedef {object} PlayerStatus What the Play buttons show.
 * @property {string|null} uri The loaded URI, null when the bar is closed.
 * @property {boolean} isPaused
 */

const ICONS = {
    previous: '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6 6h2v12H6zM9.5 12L20 6v12z"/></svg>',
    next: '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16 6h2v12h-2zM4 6l10.5 6L4 18z"/></svg>',
    close: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>',
};

/* The bar's markup. Only fixed text here: titles and credits are set with textContent. */
const HOST_HTML = `
    <style data-player-part="space"></style>
    <div class="player-inner">
        <div class="player-bar">
            <div class="player-now">
                <p class="player-label" data-player-part="label">Now playing</p>
                <a class="player-title" data-player-part="link" href="/"></a>
                <p class="player-credit" data-player-part="credit"></p>
                <p class="player-hint" data-player-part="hint" hidden></p>
            </div>
            <div class="player-controls" data-player-part="controls">
                <button type="button" class="player-control" data-player-part="previous" data-player-action="previous" aria-label="Play previous">${ICONS.previous}</button>
                <button type="button" class="player-control" data-player-part="next" data-player-action="next" aria-label="Play next">${ICONS.next}</button>
                <button type="button" class="player-control" data-player-action="close" aria-label="Close player">${ICONS.close}</button>
            </div>
        </div>
        <div class="player-consent" data-player-part="consent" hidden>
            <p id="player-consent-text">Playback runs through Spotify, which sets cookies. Continuing allows external media on this site. <a class="player-consent-link" href="${PRIVACY_URL}">Privacy &amp; cookies</a></p>
            <div class="player-consent-actions">
                <button type="button" class="player-button player-button-primary" data-player-part="continue" data-player-action="continue" aria-describedby="player-consent-text">Continue</button>
                <button type="button" class="player-button" data-player-action="cancel">Cancel</button>
            </div>
        </div>
        <div class="player-embed" data-player-part="embed"></div>
        <p class="sr-only" data-player-part="status" aria-live="polite"></p>
    </div>
`;

const state = {
    /** @type {HTMLElement|null} The bar, after <body>; null while closed. */
    host: null,
    /** @type {Record<string, HTMLElement>} The bar's elements, by data-player-part. */
    parts: {},
    /** @type {ResizeObserver|null} */
    resizeObserver: null,
    /** Space kept free under the page for the bar, in px. */
    reservedHeight: 0,
    /** @type {object|null} Spotify's EmbedController. */
    controller: null,
    /** @type {Promise<object|null>|null} */
    controllerPromise: null,
    /** @type {PlayItem|null} */
    current: null,
    /** @type {PlayItem[]} */
    queue: [],
    isAwaitingConsent: false,
    isPaused: true,
    isBuffering: false,
    /** Whether the current item has played since it was loaded. */
    hasStarted: false,
    /** Whether to call play() on the embed's next `ready`. */
    playsOnReady: false,
    hintTimer: 0,
    /** @type {HTMLElement|null} The Play button last pressed, where focus returns. */
    trigger: null,
    /** @type {PlayerStatus} The status last announced. */
    status: { uri: null, isPaused: true },
};

/** @type {Promise<object>|null} Spotify's IFrameAPI, loaded once. */
let spotifyApi = null;

/**
 * @param {HTMLElement} button A [data-play] button.
 * @returns {PlayItem}
 */
function readItem(button) {
    return {
        uri: button.dataset.play ?? '',
        title: button.dataset.playTitle ?? '',
        credit: button.dataset.playCredit ?? '',
        url: button.dataset.playUrl ?? '',
    };
}

/**
 * The page's Play buttons, one per URI, in page order.
 *
 * @returns {PlayItem[]}
 */
function collectQueue() {
    const items = new Map();

    for (const button of document.body.querySelectorAll('[data-play]')) {
        const item = readItem(button);

        if (item.uri !== '' && !items.has(item.uri)) {
            items.set(item.uri, item);
        }
    }

    return [...items.values()];
}

/**
 * @param {PlayItem} item
 */
function describe(item) {
    return item.credit === '' ? item.title : `${item.title} by ${item.credit}`;
}

/* ------------------------------------------------------------------ Bar -- */

function createHost() {
    const host = document.createElement('div');
    host.setAttribute(HOST_ATTRIBUTE, '');
    host.className = 'player';
    host.setAttribute('role', 'region');
    host.setAttribute('aria-label', 'Music player');
    host.innerHTML = HOST_HTML;

    state.parts = Object.fromEntries(
        Array.from(host.querySelectorAll('[data-player-part]'), (element) => [element.dataset.playerPart, element]),
    );

    host.addEventListener('click', onHostClick);
    host.addEventListener('keydown', onHostKeydown);

    document.documentElement.append(host);
    state.host = host;

    state.resizeObserver = new ResizeObserver(() => reserveSpace(host.offsetHeight));
    state.resizeObserver.observe(host);
}

/**
 * Keeps the end of the page, and anything scrolled into view, clear of the
 * bar; --sns-player-space lifts the cookie banner above it. The rule lives
 * in the bar's own <style>: <body> and its attributes are replaced on every
 * visit, and Livewire copies <html>'s attributes from the new page.
 *
 * @param {number} height
 */
function reserveSpace(height) {
    const pixels = Math.ceil(height);

    if (state.parts.space === undefined || pixels === state.reservedHeight) {
        return;
    }

    state.reservedHeight = pixels;
    state.parts.space.textContent = `:root { --sns-player-space: ${pixels}px; } @media screen { body { padding-bottom: ${pixels}px; } html { scroll-padding-bottom: ${pixels}px; } }`;
}

/**
 * @param {'consent'|'player'} view
 */
function showView(view) {
    const isConsent = view === 'consent';

    state.parts.label.textContent = isConsent ? 'Before you play' : 'Now playing';
    state.parts.consent.hidden = !isConsent;
    state.parts.controls.hidden = isConsent;
    state.parts.embed.hidden = isConsent;
}

function renderItem() {
    const { title, credit, url } = state.current;
    const { link, credit: creditLine } = state.parts;

    link.textContent = title;
    link.href = url || '/';
    creditLine.textContent = credit;
    creditLine.hidden = credit === '';
}

function queueIndex() {
    return state.queue.findIndex((item) => item.uri === state.current?.uri);
}

/*
 * aria-disabled rather than disabled: a disabled button would drop the
 * keyboard focus at the end of the queue.
 */
function renderQueueControls() {
    const index = queueIndex();

    state.parts.previous.setAttribute('aria-disabled', String(index <= 0));
    state.parts.next.setAttribute('aria-disabled', String(index === -1 || index >= state.queue.length - 1));
}

/**
 * Read out by screen readers. Set a moment later, so a live region that has
 * just been added with the bar is heard too.
 *
 * @param {string} message
 */
function announce(message) {
    const status = state.parts.status;

    setTimeout(() => {
        status.textContent = message;
    }, 100);
}

/**
 * @param {string} message
 */
function showHint(message) {
    state.parts.hint.textContent = message;
    state.parts.hint.hidden = false;
    announce(message);
}

function clearHint() {
    clearTimeout(state.hintTimer);

    if (state.parts.hint) {
        state.parts.hint.hidden = true;
        state.parts.hint.textContent = '';
    }
}

/**
 * Back to a Play button, without scrolling the page: the pressed one, or
 * else one for the same URI on the current page.
 *
 * @param {string} uri
 */
function focusPlayButton(uri) {
    const trigger = state.trigger?.isConnected ? state.trigger : null;
    const target = trigger?.dataset.play === uri
        ? trigger
        : (document.body.querySelector(`[data-play="${CSS.escape(uri)}"]`) ?? trigger);

    target?.focus({ preventScroll: true });
}

/* ------------------------------------------------------------- Spotify -- */

/**
 * @returns {Promise<object>} Spotify's IFrameAPI.
 */
function loadSpotifyApi() {
    spotifyApi ??= new Promise((resolve, reject) => {
        const timeout = setTimeout(() => reject(new Error('The Spotify iFrame API timed out.')), SPOTIFY_API_TIMEOUT_MS);

        // Defined before the script runs, which calls it once the API is ready.
        window.onSpotifyIframeApiReady = (api) => {
            clearTimeout(timeout);
            resolve(api);
        };

        const script = document.createElement('script');
        script.src = SPOTIFY_API_URL;
        script.async = true;
        script.addEventListener('error', () => {
            clearTimeout(timeout);
            reject(new Error('The Spotify iFrame API could not be loaded.'));
        });
        document.head.append(script);
    }).catch((error) => {
        spotifyApi = null;

        throw error;
    });

    return spotifyApi;
}

/**
 * The embed, created once per opening of the bar. It is created without a
 * URI and then given one with loadEntity(), like every later item: passing
 * `uri` to createController() would load the embed twice.
 *
 * @returns {Promise<object|null>} null when the bar was closed meanwhile.
 */
function getController() {
    if (state.controllerPromise === null) {
        const embed = state.parts.embed;

        state.controllerPromise = loadSpotifyApi().then((api) => createController(api, embed));
    }

    return state.controllerPromise;
}

/**
 * @param {object} api Spotify's IFrameAPI.
 * @param {HTMLElement} embed The bar's embed container.
 * @returns {Promise<object|null>}
 */
function createController(api, embed) {
    if (!embed.isConnected) {
        return Promise.resolve(null);
    }

    return new Promise((resolve) => {
        const placeholder = document.createElement('div');
        embed.replaceChildren(placeholder);

        api.createController(placeholder, { width: '100%', height: EMBED_HEIGHT }, (controller) => {
            controller.addListener('ready', onReady);
            controller.addListener('playback_update', onPlaybackUpdate);
            controller.addListener('error', onPlayerError);
            embed.querySelector('iframe')?.setAttribute('title', 'Spotify player');

            state.controller = controller;
            resolve(controller);
        });
    });
}

/**
 * @param {object} controller
 * @param {string} uri
 */
function loadInto(controller, uri) {
    if (typeof controller.loadEntity === 'function') {
        controller.loadEntity(uri);
    } else {
        controller.loadUri(uri);
    }
}

/* Fired each time the embed has loaded an item. */
function onReady() {
    if (!state.playsOnReady) {
        return;
    }

    state.playsOnReady = false;
    state.controller.play();
    watchForBlockedPlayback();
}

/*
 * Browsers may refuse play() (Safari, sometimes Chrome): the embed then waits
 * for its own play button.
 */
function watchForBlockedPlayback() {
    clearTimeout(state.hintTimer);

    state.hintTimer = setTimeout(() => {
        if (state.hasStarted) {
            return;
        }

        if (state.isBuffering) {
            watchForBlockedPlayback();

            return;
        }

        showHint('Press play in the Spotify player');
    }, PLAY_HINT_DELAY_MS);
}

/**
 * Sent several times a second while playing: only a change of state is
 * rendered and announced.
 *
 * @param {{ data?: { isPaused?: boolean, isBuffering?: boolean } }} event
 */
function onPlaybackUpdate({ data }) {
    const isPaused = Boolean(data?.isPaused);
    const isBuffering = Boolean(data?.isBuffering);

    state.isBuffering = isBuffering;

    if (!isPaused && !isBuffering && !state.hasStarted) {
        state.hasStarted = true;
        clearHint();
    }

    if (isPaused !== state.isPaused) {
        state.isPaused = isPaused;
        announceStatus();
    }
}

function onPlayerError() {
    state.playsOnReady = false;
    showHint('Spotify could not load this.');
}

/* --------------------------------------------------------------- Status -- */

/**
 * Sends `sns:player` when what the Play buttons show has changed.
 */
function announceStatus() {
    const status = {
        uri: state.current && !state.isAwaitingConsent ? state.current.uri : null,
        isPaused: state.isPaused,
    };

    if (status.uri === state.status.uri && status.isPaused === state.status.isPaused) {
        return;
    }

    state.status = status;
    document.dispatchEvent(new CustomEvent('sns:player', { detail: { ...status } }));
}

/**
 * @param {Element} element
 * @param {string} name
 * @param {string|null} value null removes the attribute.
 */
function setAttributeIfChanged(element, name, value) {
    if (value === null) {
        element.removeAttribute(name);
    } else if (element.getAttribute(name) !== value) {
        element.setAttribute(name, value);
    }
}

/**
 * Marks the Play buttons (and the hero's record) of the loaded URI:
 * data-state "playing" or "paused", aria-pressed while playing.
 *
 * @param {PlayerStatus} status
 */
function syncPlayButtons({ uri, isPaused } = state.status) {
    for (const element of document.querySelectorAll('[data-play], [data-play-vinyl]')) {
        const isLoaded = uri !== null && (element.dataset.play ?? element.dataset.playVinyl) === uri;

        setAttributeIfChanged(element, 'data-state', isLoaded ? (isPaused ? 'paused' : 'playing') : null);

        if (element.hasAttribute('data-play')) {
            setAttributeIfChanged(element, 'aria-pressed', String(isLoaded && !isPaused));
        }
    }
}

/* -------------------------------------------------------------- Actions -- */

/**
 * @param {PlayItem} item
 */
function play(item) {
    if (state.host === null) {
        createHost();
    }

    state.current = item;
    renderItem();

    if (state.controller === null && !hasMediaConsent()) {
        state.isAwaitingConsent = true;
        showView('consent');
        state.parts.continue.focus();

        return;
    }

    load(item);
}

/**
 * @param {PlayItem} item
 */
function load(item) {
    Object.assign(state, {
        current: item,
        isAwaitingConsent: false,
        isPaused: true,
        isBuffering: false,
        hasStarted: false,
        playsOnReady: true,
    });

    clearHint();
    showView('player');
    renderItem();
    renderQueueControls();
    announce(`Now playing: ${describe(item)}`);
    announceStatus();

    getController()
        .then((controller) => {
            if (controller !== null && state.current === item) {
                loadInto(controller, item.uri);
            }
        })
        .catch(() => {
            if (state.current === item) {
                state.playsOnReady = false;
                state.controllerPromise = null;
                showHint('The Spotify player could not load.');
            }
        });
}

/**
 * @param {-1|1} offset
 */
function step(offset) {
    const index = queueIndex();
    const item = index === -1 ? undefined : state.queue[index + offset];

    if (item !== undefined) {
        load(item);
    }
}

function close() {
    const uri = state.current?.uri;
    const hadFocus = state.host?.contains(document.activeElement) ?? false;

    clearHint();

    if (state.controller !== null) {
        state.controller.pause();
        state.controller.destroy();
    }

    state.resizeObserver?.disconnect();
    state.host?.remove();

    Object.assign(state, {
        host: null,
        parts: {},
        resizeObserver: null,
        reservedHeight: 0,
        controller: null,
        controllerPromise: null,
        current: null,
        isAwaitingConsent: false,
        isPaused: true,
        isBuffering: false,
        hasStarted: false,
        playsOnReady: false,
    });

    announceStatus();

    if (hadFocus && uri) {
        focusPlayButton(uri);
    }
}

function continueAfterConsent() {
    // Announced as `sns:consent`, which loads the item (onConsentChange).
    grantMediaConsent();

    if (state.isAwaitingConsent) {
        load(state.current);
    }

    if (state.trigger?.isConnected) {
        state.trigger.focus({ preventScroll: true });
    }
}

/* --------------------------------------------------------------- Events -- */

/**
 * External media allowed while the bar asks: the item loads. Withdrawn (or
 * refused in the banner) while the bar is open: the bar closes.
 *
 * @param {CustomEvent<{ media: boolean }|null>} event sns:consent
 */
function onConsentChange(event) {
    if (state.host === null) {
        return;
    }

    if (event.detail?.media === true) {
        if (state.isAwaitingConsent) {
            load(state.current);
        }

        return;
    }

    close();
}

/**
 * A Play button: the loaded item toggles play and pause; any other item is
 * loaded, with the page's Play buttons as the new queue.
 *
 * @param {MouseEvent} event
 */
function onDocumentClick(event) {
    const button = event.target instanceof Element ? event.target.closest('[data-play]') : null;

    if (button === null || !button.dataset.play) {
        return;
    }

    const item = readItem(button);
    const isLoaded = state.current?.uri === item.uri && !state.isAwaitingConsent;

    state.trigger = button;

    if (isLoaded && state.playsOnReady) {
        return;
    }

    if (isLoaded && state.controller !== null) {
        state.controller.togglePlay();

        return;
    }

    state.queue = collectQueue();
    play(item);
}

/**
 * @param {MouseEvent} event
 */
function onHostClick(event) {
    const target = event.target instanceof Element ? event.target : null;
    const link = target?.closest('a[href]');

    if (link) {
        followLink(event, link);

        return;
    }

    const button = target?.closest('[data-player-action]');

    if (button?.getAttribute('aria-disabled') === 'true') {
        return;
    }

    switch (button?.dataset.playerAction) {
        case 'previous':
            step(-1);
            break;
        case 'next':
            step(1);
            break;
        case 'close':
        case 'cancel':
            close();
            break;
        case 'continue':
            continueAfterConsent();
            break;
    }
}

/**
 * @param {KeyboardEvent} event
 */
function onHostKeydown(event) {
    if (event.key === 'Escape' && state.isAwaitingConsent) {
        event.preventDefault();
        close();
    }
}

/**
 * The bar's links (the title, Privacy & cookies) work like wire:navigate
 * links. They are handled here because Livewire sets its links up inside
 * <body>, and the bar lives outside it.
 *
 * @param {MouseEvent} event
 * @param {HTMLAnchorElement} link
 */
function followLink(event, link) {
    const isPlainClick = event.button === 0 && !event.metaKey && !event.ctrlKey && !event.shiftKey && !event.altKey;

    if (!isPlainClick || event.defaultPrevented || typeof window.Livewire?.navigate !== 'function') {
        return;
    }

    event.preventDefault();
    window.Livewire.navigate(link.href);
}

/**
 * Livewire keeps each page's HTML for back and forward, taken from
 * document.documentElement, so it holds a copy of the bar. Restored, the
 * copy lands at the end of the new <body>; it goes before the page is shown
 * (its lazy iframe never starts loading). The real bar is known by reference.
 */
function removeSnapshotCopies() {
    for (const copy of document.body.querySelectorAll(`[${HOST_ATTRIBUTE}]`)) {
        if (copy !== state.host) {
            copy.remove();
        }
    }
}

/**
 * @param {(livewire: object) => void} callback
 */
function whenLivewireIsLoaded(callback) {
    if (window.Livewire) {
        callback(window.Livewire);
    } else {
        document.addEventListener('livewire:init', () => callback(window.Livewire), { once: true });
    }
}

/**
 * @returns {{ close: () => void, readonly status: PlayerStatus, readonly host: HTMLElement|null }}
 */
export function startPlayer() {
    document.addEventListener('click', onDocumentClick);
    document.addEventListener('sns:player', (event) => syncPlayButtons(event.detail));
    document.addEventListener('sns:consent', onConsentChange);
    document.addEventListener('livewire:navigating', (event) => event.detail.onSwap(removeSnapshotCopies));
    // A new page, and a component re-rendered by Livewire (e.g. the filters
    // on /releases), come with the buttons' server state.
    document.addEventListener('livewire:navigated', () => syncPlayButtons());
    whenLivewireIsLoaded((livewire) => livewire.hook('morphed', () => syncPlayButtons()));

    return {
        close,
        get status() {
            return { ...state.status };
        },
        get host() {
            return state.host;
        },
    };
}
