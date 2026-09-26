/**
 * What wire:navigate visits need beyond Livewire's own page swap (it replaces
 * <body> without a reload, so the footer player keeps playing):
 *
 * - a single JSON-LD block in <head>: Livewire adds the new page's block but
 *   never removes the previous ones;
 * - the #fragment of the link followed (/about#gallery): Livewire always
 *   scrolls to the top.
 *
 * The gallery lightbox is rebound in app.js, and the player removes its own
 * copies from back/forward snapshots in player.js.
 */

const JSON_LD_SELECTOR = 'script[type="application/ld+json"]';

/** @type {string} The fragment to scroll to once the visit has finished, e.g. "#gallery". */
let fragmentToReveal = '';

/**
 * @returns {HTMLScriptElement[]}
 */
function jsonLdBlocks() {
    return Array.from(document.head.querySelectorAll(JSON_LD_SELECTOR));
}

/**
 * Livewire appends the new page's JSON-LD to <head> unless an identical block
 * is already there, and never removes one. So the blocks present before the
 * swap go once a new block has been added; when none was added, the block
 * already there is the new page's (there is only ever one).
 *
 * @param {CustomEvent<{ onSwap: (callback: () => void) => void }>} event livewire:navigating
 */
function keepOnlyTheNewJsonLd(event) {
    const previousBlocks = jsonLdBlocks();

    event.detail.onSwap(() => {
        const blocks = jsonLdBlocks();
        const newBlock = blocks.filter((block) => !previousBlocks.includes(block)).at(-1);

        if (newBlock === undefined) {
            return;
        }

        blocks.filter((block) => block !== newBlock).forEach((block) => block.remove());
    });
}

/**
 * @param {CustomEvent<{ url: URL, history: boolean }>} event livewire:navigate
 */
function rememberFragment(event) {
    // Back and forward restore the previous scroll position instead.
    fragmentToReveal = event.detail.history ? '' : event.detail.url.hash;
}

function revealFragment() {
    const fragment = fragmentToReveal;
    fragmentToReveal = '';

    if (fragment.length < 2) {
        return;
    }

    let id;

    try {
        id = decodeURIComponent(fragment.slice(1));
    } catch {
        return;
    }

    const target = document.getElementById(id);

    // A frame later, after Livewire's own scroll to the top.
    if (target) {
        requestAnimationFrame(() => target.scrollIntoView());
    }
}

export function startNavigation() {
    document.addEventListener('livewire:navigating', keepOnlyTheNewJsonLd);
    document.addEventListener('livewire:navigate', rememberFragment);
    document.addEventListener('livewire:navigated', revealFragment);
}
