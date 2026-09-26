/**
 * Consent for analytics cookies and external media (ePrivacy art. 5(3),
 * GDPR art. 6(1)(a)). Nothing that needs consent loads before the visitor
 * chooses.
 *
 * - The choice is kept in localStorage under `sns.consent` as
 *   { version, decidedAt, analytics, media }. It lasts six months, or until
 *   VERSION changes; the banner then asks again. Where storage is blocked it
 *   lasts for the visit.
 * - The banner (x-site.consent) is rendered hidden in every page and shown
 *   here while there is no choice. Accept all, Reject all and Customize sit
 *   on its first level; any [data-consent-open] button reopens its second
 *   level, the Cookie settings.
 * - Each change is announced as a `sns:consent` event on document, with the
 *   choice as its detail. The footer player (player.js) reads `media` too.
 * - Analytics: gtag.js is injected only once analytics is accepted, with the
 *   id of <meta name="sns-ga-id"> (production only), in Google's basic
 *   consent mode. Withdrawn, it is told so and the _ga cookies are deleted.
 * - Players in posts (App\Support\ArticleHtml) are placeholders until
 *   external media is allowed, or until "Load player" loads that one alone.
 *
 * Every page's <body> is replaced on wire:navigate visits, so the listeners
 * are set on document and the page is brought up to date on each
 * `livewire:navigated`.
 */

const STORAGE_KEY = 'sns.consent';
const LEGACY_MEDIA_KEY = 'sns.consent.externalMedia';
const VERSION = 1;
const MAX_AGE_MS = 1000 * 60 * 60 * 24 * 182;
const GA_SCRIPT_URL = 'https://www.googletagmanager.com/gtag/js';
const GA_COOKIE = /^_ga(_.+)?$/;
const EMBED_HOSTS = ['open.spotify.com', 'embed.beatport.com', 'nfan.link'];
const EMBED_ALLOW = 'autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture';
const EMBED_DEFAULT_HEIGHT = 352;

/**
 * @typedef {object} ConsentChoice
 * @property {number} version
 * @property {string} decidedAt ISO 8601 date of the choice.
 * @property {boolean} analytics
 * @property {boolean} media External media: Spotify, Beatport and nfan.link players.
 */

/** @type {ConsentChoice|null} The visitor's choice; null until one is made. */
let choice = null;

let isAnalyticsLoaded = false;

/** @type {HTMLElement|null} The button that opened the settings, where focus returns. */
let settingsTrigger = null;

/* -------------------------------------------------------------- Storage -- */

/**
 * @param {unknown} value
 * @returns {ConsentChoice|null} The stored choice, if still valid.
 */
function validChoice(value) {
    if (value === null || typeof value !== 'object' || value.version !== VERSION || typeof value.decidedAt !== 'string') {
        return null;
    }

    const decidedAt = Date.parse(value.decidedAt);
    const age = Date.now() - decidedAt;

    if (Number.isNaN(decidedAt) || age > MAX_AGE_MS || age < -MAX_AGE_MS) {
        return null;
    }

    return {
        version: VERSION,
        decidedAt: value.decidedAt,
        analytics: value.analytics === true,
        media: value.media === true,
    };
}

/**
 * @returns {ConsentChoice|null}
 */
function readChoice() {
    try {
        return validChoice(JSON.parse(window.localStorage.getItem(STORAGE_KEY) ?? 'null'));
    } catch {
        return null;
    }
}

/**
 * @param {ConsentChoice} value
 */
function storeChoice(value) {
    try {
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify(value));
    } catch {
        // Private mode or blocked storage: the choice lasts for this visit.
    }
}

/**
 * The footer player of stage 4 kept its own "granted" flag: it becomes an
 * external media choice, once, and goes.
 */
function migrateLegacyChoice() {
    try {
        const legacy = window.localStorage.getItem(LEGACY_MEDIA_KEY);

        if (legacy === null) {
            return;
        }

        window.localStorage.removeItem(LEGACY_MEDIA_KEY);

        if (legacy === 'granted' && readChoice() === null) {
            storeChoice({ version: VERSION, decidedAt: new Date().toISOString(), analytics: false, media: true });
        }
    } catch {
        // Storage is blocked: there is nothing to migrate.
    }
}

/**
 * Records a choice and applies it everywhere.
 *
 * @param {{ analytics: boolean, media: boolean }} categories
 */
function decide({ analytics, media }) {
    choice = {
        version: VERSION,
        decidedAt: new Date().toISOString(),
        analytics: Boolean(analytics),
        media: Boolean(media),
    };

    storeChoice(choice);
    applyChoice();
}

function applyChoice() {
    applyAnalytics();
    renderEmbeds();
    document.dispatchEvent(new CustomEvent('sns:consent', { detail: consentState() }));
}

/**
 * @returns {ConsentChoice|null} A copy of the current choice.
 */
export function consentState() {
    return choice === null ? null : { ...choice };
}

export function hasMediaConsent() {
    return choice?.media === true;
}

/**
 * External media allowed from outside the banner: "Continue" in the footer
 * player, "Always allow external media" on a post's player.
 */
export function grantMediaConsent() {
    decide({ analytics: choice?.analytics ?? false, media: true });
    // A choice is now recorded: the banner, if it was asking, goes.
    renderBanner();
}

/* ------------------------------------------------------------ Analytics -- */

function analyticsId() {
    const id = document.querySelector('meta[name="sns-ga-id"]')?.getAttribute('content')?.trim() ?? '';

    return /^G-[A-Z0-9]+$/i.test(id) ? id : '';
}

/* gtag.js expects the Arguments object itself, not an array. */
function gtag() {
    window.dataLayer.push(arguments);
}

/**
 * Basic consent mode: nothing from Google before consent; then the defaults
 * (all denied), the update for analytics, and the configuration. GA4 counts
 * the wire:navigate visits itself, from the history changes.
 *
 * @param {string} id
 */
function loadAnalytics(id) {
    isAnalyticsLoaded = true;
    window.dataLayer = window.dataLayer || [];
    window.gtag = window.gtag || gtag;
    window[`ga-disable-${id}`] = false;

    window.gtag('consent', 'default', {
        ad_storage: 'denied',
        ad_user_data: 'denied',
        ad_personalization: 'denied',
        analytics_storage: 'denied',
    });
    window.gtag('consent', 'update', { analytics_storage: 'granted' });
    window.gtag('js', new Date());
    window.gtag('config', id);

    const script = document.createElement('script');
    script.async = true;
    script.src = `${GA_SCRIPT_URL}?id=${encodeURIComponent(id)}`;
    document.head.append(script);
}

function applyAnalytics() {
    const id = analyticsId();
    const isGranted = choice?.analytics === true;

    if (isGranted && id !== '') {
        if (!isAnalyticsLoaded) {
            loadAnalytics(id);
        } else {
            window[`ga-disable-${id}`] = false;
            window.gtag('consent', 'update', { analytics_storage: 'granted' });
        }

        return;
    }

    if (isAnalyticsLoaded && id !== '') {
        window.gtag('consent', 'update', { analytics_storage: 'denied' });
        window[`ga-disable-${id}`] = true;
    }

    if (!isGranted) {
        deleteAnalyticsCookies();
    }
}

/**
 * The _ga cookies are set on the widest domain possible (".example.com"):
 * each is expired on every domain it may have been set on.
 */
function deleteAnalyticsCookies() {
    const names = document.cookie
        .split(';')
        .map((cookie) => cookie.split('=')[0].trim())
        .filter((name) => GA_COOKIE.test(name));

    if (names.length === 0) {
        return;
    }

    const labels = window.location.hostname.split('.');
    const domains = [''];

    for (let index = 0; index < labels.length - 1; index++) {
        const domain = labels.slice(index).join('.');
        domains.push(`; domain=${domain}`, `; domain=.${domain}`);
    }

    for (const name of names) {
        for (const domain of domains) {
            document.cookie = `${name}=; Max-Age=0; path=/${domain}`;
        }
    }
}

/* --------------------------------------------------------------- Banner -- */

function banner() {
    return document.querySelector('[data-consent-banner]');
}

/**
 * @param {HTMLElement} element
 * @param {'notice'|'settings'} view
 */
function showView(element, view) {
    const isSettings = view === 'settings';

    element.querySelector('[data-consent-view="notice"]').hidden = isSettings;
    element.querySelector('[data-consent-view="settings"]').hidden = !isSettings;
    element.setAttribute('aria-labelledby', isSettings ? 'consent-settings-title' : 'consent-title');

    if (isSettings) {
        element.removeAttribute('aria-describedby');
    } else {
        element.setAttribute('aria-describedby', 'consent-text');
    }

    for (const input of element.querySelectorAll('[data-consent-choice]')) {
        input.checked = choice?.[input.dataset.consentChoice] === true;
    }

    // Closing without a choice is only possible once there is one.
    element.querySelector('[data-consent-action="close"]').hidden = choice === null;
    element.hidden = false;
}

/**
 * @param {'notice'|'settings'} view
 * @param {boolean} [moveFocus]
 */
function openBanner(view, moveFocus = false) {
    const element = banner();

    if (element === null) {
        return;
    }

    showView(element, view);

    if (moveFocus) {
        element.querySelector(view === 'settings' ? '#consent-settings-title' : '#consent-title')?.focus();
    }
}

function closeBanner() {
    const element = banner();

    if (element === null || element.hidden) {
        return;
    }

    const hadFocus = element.contains(document.activeElement);
    element.hidden = true;

    if (hadFocus && settingsTrigger?.isConnected) {
        settingsTrigger.focus();
    }

    settingsTrigger = null;
}

/**
 * The banner as the page arrives: shown while there is no choice.
 */
function renderBanner() {
    const element = banner();

    if (element === null) {
        return;
    }

    if (choice === null) {
        showView(element, 'notice');
    } else {
        element.hidden = true;
    }
}

/**
 * @param {string} action
 */
function runAction(action) {
    const element = banner();

    switch (action) {
        case 'accept':
            decide({ analytics: true, media: true });
            closeBanner();
            break;
        case 'reject':
            decide({ analytics: false, media: false });
            closeBanner();
            break;
        case 'save':
            decide({
                analytics: element?.querySelector('[data-consent-choice="analytics"]')?.checked === true,
                media: element?.querySelector('[data-consent-choice="media"]')?.checked === true,
            });
            closeBanner();
            break;
        case 'customize':
            openBanner('settings', true);
            break;
        case 'close':
            if (choice !== null) {
                closeBanner();
            }
            break;
    }
}

/* --------------------------------------------------------------- Embeds -- */

/**
 * @param {string|undefined} value
 * @returns {string|null} The player's https URL, on one of the allowed hosts.
 */
function embedSource(value) {
    try {
        const url = new URL(value ?? '');

        return url.protocol === 'https:' && EMBED_HOSTS.includes(url.hostname) ? url.href : null;
    } catch {
        return null;
    }
}

/**
 * Swaps a post's placeholder for its player.
 *
 * @param {HTMLElement} figure figure[data-embed-src]
 * @param {'click'|'consent'} reason
 * @returns {HTMLIFrameElement|null}
 */
function loadEmbed(figure, reason) {
    const existing = figure.querySelector('iframe');

    if (existing !== null) {
        return existing;
    }

    const src = embedSource(figure.dataset.embedSrc);
    const notice = figure.querySelector('[data-embed-notice]');

    if (src === null || notice === null) {
        return null;
    }

    const height = Number.parseInt(figure.dataset.embedHeight ?? '', 10);
    const iframe = document.createElement('iframe');
    iframe.src = src;
    iframe.title = figure.dataset.embedTitle || `${figure.dataset.embedProvider ?? 'External'} player`;
    iframe.height = String(height > 0 && height <= 1200 ? height : EMBED_DEFAULT_HEIGHT);
    iframe.loading = 'lazy';
    iframe.allow = EMBED_ALLOW;

    notice.hidden = true;
    notice.after(iframe);
    figure.dataset.embedLoaded = reason;

    return iframe;
}

/**
 * @param {HTMLElement} figure
 */
function unloadEmbed(figure) {
    figure.querySelector('iframe')?.remove();

    const notice = figure.querySelector('[data-embed-notice]');

    if (notice !== null) {
        notice.hidden = false;
    }

    delete figure.dataset.embedLoaded;
}

/**
 * Players follow the external media choice; one loaded by a click stays.
 */
function renderEmbeds() {
    for (const figure of document.body.querySelectorAll('figure[data-embed-src]')) {
        if (hasMediaConsent()) {
            loadEmbed(figure, 'consent');
        } else if (figure.dataset.embedLoaded === 'consent') {
            unloadEmbed(figure);
        }
    }
}

/**
 * Livewire keeps the page's HTML for back and forward right after
 * `livewire:navigating`: it must hold placeholders, or going back would
 * load the players before the choice is checked again.
 */
function unloadAllEmbeds() {
    for (const figure of document.body.querySelectorAll('figure[data-embed-loaded]')) {
        unloadEmbed(figure);
    }
}

/* --------------------------------------------------------------- Events -- */

/**
 * @param {MouseEvent} event
 */
function onDocumentClick(event) {
    const target = event.target instanceof Element ? event.target : null;

    if (target === null) {
        return;
    }

    const action = target.closest('[data-consent-action]');

    if (action !== null) {
        runAction(action.dataset.consentAction);

        return;
    }

    const opener = target.closest('[data-consent-open]');

    if (opener !== null) {
        settingsTrigger = opener;
        openBanner('settings', true);

        return;
    }

    const loadButton = target.closest('[data-embed-load]');

    if (loadButton !== null) {
        const figure = loadButton.closest('figure[data-embed-src]');

        figure && loadEmbed(figure, 'click')?.focus();

        return;
    }

    const allowButton = target.closest('[data-embed-allow]');

    if (allowButton !== null) {
        const figure = allowButton.closest('figure[data-embed-src]');

        grantMediaConsent();
        figure?.querySelector('iframe')?.focus();
    }
}

/**
 * Escape leaves the Cookie settings: back to the first level while there is
 * no choice yet (the banner itself never closes without one).
 *
 * @param {KeyboardEvent} event
 */
function onDocumentKeydown(event) {
    const element = banner();

    if (event.key !== 'Escape' || element === null || element.hidden || !element.contains(document.activeElement)) {
        return;
    }

    if (element.querySelector('[data-consent-view="settings"]').hidden) {
        return;
    }

    event.preventDefault();

    if (choice !== null) {
        closeBanner();
    } else {
        openBanner('notice');
        element.querySelector('[data-consent-action="customize"]')?.focus();
    }
}

/**
 * A choice made in another tab.
 *
 * @param {StorageEvent} event
 */
function onStorage(event) {
    if (event.key !== STORAGE_KEY) {
        return;
    }

    choice = readChoice();
    applyChoice();
    renderBanner();
}

function onNavigated() {
    renderBanner();
    renderEmbeds();
}

export function startConsent() {
    migrateLegacyChoice();
    choice = readChoice();

    document.addEventListener('click', onDocumentClick);
    document.addEventListener('keydown', onDocumentKeydown);
    document.addEventListener('livewire:navigating', unloadAllEmbeds);
    // Also fired once on the first page load.
    document.addEventListener('livewire:navigated', onNavigated);
    window.addEventListener('storage', onStorage);

    applyAnalytics();
    onNavigated();

    return {
        get state() {
            return consentState();
        },
        openSettings: () => openBanner('settings', true),
    };
}
