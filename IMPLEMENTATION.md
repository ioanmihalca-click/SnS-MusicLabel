# Implementare — Cleanup, UI Polish, Tests, Featured Track & Design Refresh

> **Starea actuală a proiectului:** vezi secțiunea [„Redesign 2026 (etapele 1–7)”](#redesign-2026-etapele-17) de la finalul documentului, cu runbook-ul de producție. Secțiunile Stage A–E de mai jos descriu iterațiile anterioare și rămân ca istoric; o parte din ce descriu (hero-ul animat, stats strip, FeaturedTrack, `<x-layouts.app>` etc.) a fost înlocuită în redesign.

Această implementare s-a desfășurat în 5 stage-uri (A–E). Stage-urile A–C au fost livrate pe branch-ul `refactor/cleanup-and-polish` în 3 commit-uri; Stage D este o iterație ulterioară pe `main` („featured track în hero + resursă Filament dedicată"); Stage E este un design refresh pe branch-ul `design/refresh` (display font, logomark, motion, stats strip, card restyle). Niciun conținut, secțiune, link sau imagine existentă nu a fost eliminat. Toate cele 10 secțiuni de homepage, paginile blog și cele 5 resurse Filament inițiale rămân exact unde erau; Stage D adaugă o nouă resursă (FeaturedTrack) și un pill condiționat sub CTAs; Stage E adaugă o secțiune de stats strip și transformă vibe-ul vizual fără să modifice copy-ul.

## Sumar executiv

| Metric | Înainte | După Stage C | După Stage D | După Stage E |
|---|---|---|---|---|
| Fișiere șterse | — | 7 dead-code | (idem) | (idem) |
| Componente Blade reutilizabile | 0 | 14 | 14 | **17** (+ logomark, brand-name, stats-strip) |
| Secțiuni homepage | 10 | 10 | 10 | **11** (+ Stats Strip între Hero și About) |
| Teste | 0 | 47 (90 assertions) | 55 (108 assertions) | **55 (108 assertions)** |
| Modele | 5 | 5 | 6 | 6 |
| Resurse Filament | 5 | 5 | 6 | 6 |
| Componente Livewire | 7 | 7 | 8 | 8 |
| Display font | nimic (system) | nimic | nimic | **Big Shoulders Display 900** (+ Inter 400-700) via Bunny Fonts |
| Brand consistency | „Snow n Stuff" / „Snow 'n' Stuff" mixt | (idem) | (idem) | **uniform „Snow 'n' Stuff"** via `<x-brand-name>` |
| Bundle CSS | ~93 KB | ~92 KB | ~102 KB | **~108 KB** (+tokens, noise, equalizer, font fallbacks) |
| Bundle JS | 141 KB CDN | 142 KB local | 142.47 KB | **142.83 KB** (+parallax) |
| Acoperire focus-visible | 0 | toate links/butoane | (idem, plus CTA pill) | (idem) |

## Stage A — Cleanup (commit `8ac3ecc`)

### Fișiere șterse (verificat 0 referințe live înainte de ștergere)

- `app/Livewire/LatestArticles.php` + `resources/views/livewire/latest-articles.blade.php` — componenta era doar într-un bloc comentat `{{-- --}}` în welcome.blade.php
- Bloc comentat + tag-uri orfane `</div></section>` (linii 145–158 din welcome.blade.php) — HTML-ul era invalid
- `tests/Feature/ExampleTest.php` + `tests/Unit/ExampleTest.php` — placeholder-e Laravel default
- `resources/js/bootstrap.js` + import-ul aferent din `app.js` — seta `window.axios` care nu era citit nicăieri (Livewire are propriul AJAX)

### Cod mort eliminat în fișiere

- `app/Livewire/Hero.php`: 5 proprietăți publice nefolosite + `$this->dispatch('startAnimations')` orfan (zero listeners)
- `app/Livewire/BlogIndex.php`: `public $debugSql = ''`, înlocuit `protected $queryString` cu atribut `#[Url(except: '')]`, fix bug latent în where/orWhere wrap (parantezat acum în closure)

### Schimbări de dependențe

- **composer.json**: `laravel/tinker` mutat din `require` în `require-dev` (tool de dev, nu trebuie livrat în producție)
- **package.json**: eliminat `axios` (nefolosit), adăugat `@fancyapps/ui ^5.0.36` ca devDependency
- Fancybox import-at via Vite (CSS și JS); cele 2 tag-uri CDN `cdn.jsdelivr.net` din welcome.blade.php au fost eliminate

## Stage B — UI Polish & Refactoring (commit `5b28881`)

### Layout partajat

Înainte: `welcome.blade.php` era `<!DOCTYPE>` raw cu head + favicons + gtag + Vite + JSON-LD + preloader + back-to-top duplicate cu `layouts/blog.blade.php` (drift-ul deja începuse — preloader-ele difereau).

Acum:
- `<x-partials.head ...>` — head shared cu props pentru title/description/keywords/og:image/preload-image, slot pentru JSON-LD page-specific
- `<x-partials.preloader />` — preloader-ul cu cele 3 ringuri concentrice + `role="status"` + `aria-live="polite"`
- `<x-back-to-top />` — buton back-to-top unic, brand-consistent, cu `aria-label` + focus ring
- `<x-layouts.app>` — layout-ul homepage care include head + body + preloader + back-to-top + top-bar + slot + footer
- `<x-layouts.blog>` — refactor-at să folosească aceleași partials

`welcome.blade.php` a trecut de la 156 linii la 11 linii.

### Componente reutilizabile

11 componente noi în `resources/views/components/icons/`:
- `check`, `spotify`, `envelope`, `arrow-up`, `arrow-right`, `chevron-left`, `chevron-right`
- `social/twitter`, `social/facebook`, `social/instagram`, `social/linkedin`

Plus `<x-section-header eyebrow="..." title="..." :gradient="..." />` care înlocuiește 5 copii ale pattern-ului `<h2 uppercase>...</h2><p text-4xl text-red-800>...</p>`.

Rezultat: ~400 linii de SVG inline duplicate colaps la câteva tag-uri `<x-…/>`. Aspect 100% identic.

### Hero refactor

- 5x `setTimeout` chained + `:style="..."` ternaries cu typo-uri (`'opacity 1; transform: ...'`) → un singur `x-data` cu array de stage flags + clase Tailwind (`opacity-0 translate-y-4` → `opacity-100 translate-y-0`)
- Intervalele reduse de la 500 ms la 220 ms (perceived performance: 2.5s → ~1.3s)
- Spotify icon → `<x-icons.spotify>`
- Focus-visible rings pe cele 3 CTAs

### Read-more pe server (Releases + Artists)

Approach-ul anterior citea `$refs.fullDescription.textContent` în Alpine și trunchia client-side — fragil pentru rich-text HTML.

Acum:
- `App\Livewire\Releases` și `App\Livewire\Artists` trunchiază cu `Str::limit(strip_tags(...), 220)` și expun `plain_description`, `short_description`, `is_truncated`
- `Releases::render()` adaugă `latest()->limit(24)` — bound query-ul
- Blade folosește `x-show` pe text simplu, scapă de `x-html` cu `textContent` round-trip

### Accesibilitate (B5/B6)

Pe toate elementele interactive:
- `focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 focus-visible:ring-offset-black`

Specific:
- Mobile menu button: `aria-expanded` reactiv, `aria-controls="mobile-menu"`, `aria-label="Toggle navigation menu"`
- Preloader: `role="status" aria-live="polite" aria-label="Loading"`
- Slider prev/next: `aria-label="Previous playlist"` / `"Next playlist"`; dots active au `aria-current="true"`
- Share buttons (blog-show): `aria-label="Share on Twitter"` etc. (titlu păstrat)
- Photo gallery: `<a>` are `aria-label="Open {title} in lightbox"`
- Photo gallery: eliminat `id="gallery"` duplicat (era pe section ȘI pe grid → invalid HTML)

### Optimizare imagini (B7)

- `width` și `height` explicite pe fiecare `<img>` → previne CLS
- `loading="lazy" decoding="async"` pe imaginile din artists, blog cards, photo gallery
- Hero blog (`blog-show`): `loading="eager" fetchpriority="high"` (LCP)
- `preload-image="/assets/img/music-bg.jpg"` propagat ca prop la `<x-layouts.app>` → `<link rel="preload" as="image" fetchpriority="high">` în head

### CSS variables (B11) + animații scroll-triggered (B9)

`resources/css/app.css`:
- Definite `--color-red-900` și `--color-red-800` (referențiate dar nedefinite înainte)
- CSS pentru `[data-reveal]` → `[data-reveal][data-revealed]` cu transition opacity/transform
- Respect `prefers-reduced-motion`

`resources/js/app.js`:
- IntersectionObserver care setează `data-revealed="true"` pe elementele cu `[data-reveal]` la 12% intersect
- Fallback pentru browsere fără IntersectionObserver (revelăre instant)
- Re-bindare la `livewire:navigated`

Aplicat pe: `<x-about>`, `<livewire:artists>`, `<livewire:releases>`, `<livewire:playlist-slider>`, `<livewire:photo-gallery>`, `<x-contact>`.

### Filament polish (B10)

`AdminPanelProvider`:
- `brandName("Snow 'n' Stuff Admin")`
- `favicon(asset('assets/favicon/favicon.ico'))`
- `darkMode(true)`
- `sidebarCollapsibleOnDesktop()`

Per resursă:
- **ArtistResource** — `navigationGroup: 'Catalogue'`, `searchable()` pe name, `sortable()` pe order, `reorderable('order')`, BulkActionGroup, form sections
- **ReleaseResource** — `searchable()` pe title, `sortable()` pe `created_at`, `TernaryFilter` "has description", `IconColumn::make('has_description')`, form sections, autosize textarea
- **PlaylistResource** — form sections, `TernaryFilter` "is_active", `BulkActionGroup`
- **PhotoResource** — `navigationGroup: 'Media'`, image preview `square()->width(80)`, form section
- **BlogResource** — `navigationGroup: 'Content'`, `searchable()`, 3 filtre (Published/Scheduled/Drafts), `IconColumn::make('is_published')` derivat din `published_at <= now()`, bulk actions custom (Publish now / Unpublish), SEO section colapsibilă, image preview

### Blog model

- Migrat `$casts` la metoda `casts()` (Laravel 12 idiom)
- Migrat `setTitleAttribute()` (legacy mutator) la `Attribute::set` modern
- **Bug fix**: noul mutator păstrează slug-ul existent dacă există (`$attributes['slug'] ?? Str::slug($value)`) — înainte, fiecare edit de title rescria slug-ul rupând URL-urile publice indexate

## Stage C — Tests (commit `7ca58c8`)

### Configurare

- `tests/Pest.php` — `LazilyRefreshDatabase` pentru toate testele Feature
- `phpunit.xml` — uncomment-at `DB_CONNECTION=sqlite` + `DB_DATABASE=:memory:` (izolare totală față de DB-ul de dev)

### Factories noi

5 factories în `database/factories/` (toate cu state methods unde relevant):
- `ArtistFactory`, `ReleaseFactory`, `PlaylistFactory` (`inactive()`)
- `BlogFactory` (`published()`, `scheduled()`, `draft()`)
- `PhotoFactory`

### 47 teste, 90 assertions, toate verzi

| Suite | Fișier | Acoperire |
|---|---|---|
| Routes | `HomePageTest` | 200, JSON-LD, **assertSeeText pentru fiecare titlu de secțiune (guardrail "nu dispare nimic")** |
| Routes | `BlogIndexTest` | 200, listing, scheduled exclus, empty state |
| Routes | `BlogShowTest` | 200 valid slug, 404 future, 404 missing, related fără duplicat |
| Livewire | `HeroTest` | render + brand text |
| Livewire | `ArtistsTest` | order ordering, server truncation flags, short bios untouched |
| Livewire | `ReleasesTest` | latest-first via viewData, `limit(24)` cap, truncation flags |
| Livewire | `PlaylistSliderTest` | only is_active, nextSlide/previousSlide cycle wrap, goToSlide |
| Livewire | `PhotoGalleryTest` | paginate la 12, latest-first |
| Livewire | `BlogIndexComponentTest` | published-only, search title+content, pagination, reset paginators.page |
| Filament | `AdminAccessTest` | guest redirect, 403 non-admin, 200 admin |
| Filament | `ArtistResourceTest` | index/create/edit smoke |
| Filament | `ReleaseResourceTest` | index/create/edit smoke |
| Filament | `PlaylistResourceTest` | index/create/edit smoke |
| Filament | `PhotoResourceTest` | index/create/edit smoke |
| Filament | `BlogResourceTest` | smoke + slug păstrat la edit + slug auto-generat la create |

```
$ php artisan test --compact
.................................................

Tests:    47 passed (90 assertions)
Duration: 7.01s
```

## Stage D — Featured Track (pe `main`, post Stage C)

Cerința explicită utilizator: *„în hero sus ar trebui să apară cea mai nouă piesă promovată de label și să avem o resursă dedicată în Filament pentru asta."* Documentat în planul comprehensiv ca Section D, implementat acum.

### Decizii confirmate cu utilizatorul

| Decizie | Răspuns |
|---|---|
| Plasare în hero | Pill compact sub CTAs (păstrează brand reveal-ul intact) |
| Input Spotify | URL only — `spotify_track_url`; track ID + embed src derivate cu regex pentru viitor |
| Strategie active | Single active la un moment dat (`->first()` ordered by `order` ASC apoi `released_at` DESC) |
| Cover image | FileUpload opțional pe disk-ul `public`, directorul `featured-tracks/` |

### Model & migrare

`database/migrations/2026_05_09_180007_create_featured_tracks_table.php`:

```php
$table->id();
$table->string('title');
$table->string('artist_name');
$table->string('spotify_track_url');
$table->string('cover_image')->nullable();
$table->date('released_at')->nullable();
$table->unsignedInteger('order')->default(0);
$table->boolean('is_active')->default(true);
$table->timestamps();
$table->index(['is_active', 'order']);
```

`App\Models\FeaturedTrack`:
- `casts()` method (Laravel 12 idiom — ca în `Blog`): `released_at => date`, `order => integer`, `is_active => boolean`.
- Scopes: `scopeActive()` (`where is_active = true`), `scopeOrdered()` (`order ASC, released_at DESC`).
- Accessors: `getSpotifyTrackIdAttribute()` extrage ID-ul de 22 caractere din URL (regex `#/track/([A-Za-z0-9]+)#`); `getSpotifyEmbedSrcAttribute()` returnează URL-ul de embed derivat — pregătite pentru o eventuală variantă cu inline player fără refactoring.

`database/factories/FeaturedTrackFactory.php` cu `inactive()` state, mirror `PlaylistFactory`.

### Resursa Filament

`app/Filament/Resources/FeaturedTrackResource.php`:
- `navigationGroup: 'Catalogue'`, `navigationSort: 25` (între Release=20 și Playlist=30), `navigationIcon: 'heroicon-o-star'`, `navigationLabel: 'Featured Track'`.
- Form, 3 secțiuni: **Track** (title + artist_name, 2 col), **Spotify** (`TextInput` cu `->url()` + helper text), **Display** (FileUpload `cover_image` directory `featured-tracks/`, DatePicker `released_at`, integer `order`, Toggle `is_active`).
- Tabela: ImageColumn cover (square 60), Text title/artist searchable+sortable, order width(60) sortable, IconColumn `is_active`, released_at date sortable toggleable.
- `defaultSort('order')`, `reorderable('order')` (drag-drop ca la Playlist), `TernaryFilter::make('is_active')`, BulkActionGroup cu DeleteBulkAction.
- Pages standard: `ListFeaturedTracks`, `CreateFeaturedTrack`, `EditFeaturedTrack` (cu DeleteAction header).

### Componenta Livewire & view

`app/Livewire/FeaturedTrack.php` — un singur `render()` care folosește scopurile noi:

```php
$track = FeaturedTrackModel::query()->active()->ordered()->first();
return view('livewire.featured-track', ['track' => $track]);
```

`resources/views/livewire/featured-track.blade.php`:
- Guardrail `@if ($track)` — dacă nu există track activ, randează `<div></div>` gol → hero-ul nu se sparge.
- Pill: container glass `bg-white/5 backdrop-blur border border-red-800/20` cu cover 64×64 (sau fallback gradient cu inițiala titlului), eyebrow uppercase „Now Spinning" (red-500 tracking-widest), linia titlu — artist, link „Listen on Spotify" cu `<x-icons.spotify />` (reuse Stage B), `target="_blank" rel="noopener noreferrer"`, `aria-label` descriptiv, focus-visible ring identic cu restul site-ului.
- Imaginea folosește `width/height/loading=lazy/decoding=async` (politică Stage B).

### Integrare în hero

`resources/views/livewire/hero.blade.php`:
- Array-ul de stages extins de la 5 la 6: `['welcome', 'brand', 'subtitle', 'desc', 'buttons', 'featuredTrack']`. Cu același stagger de 220 ms, pill-ul apare la ~1300 ms după mount — *după* CTAs.
- `<livewire:featured-track />` injectat într-un wrapper Alpine cu fade-in din `translate-y-4` (mirror exact al stilului existent al butoanelor).
- Brand reveal (gradientul „Snow n Stuff") rămâne neatins.

### Tests

3 fișiere noi/modificate, **+8 cazuri** (47 → 55 teste, 90 → 108 assertions):

| Fișier | Cazuri |
|---|---|
| `tests/Feature/Livewire/FeaturedTrackTest.php` | 5 — render fără record (track=null), render cu titlu+artist, ignore inactive, ordering corect (`order ASC, released_at DESC`), parsing URL → track ID + embed src |
| `tests/Feature/Filament/FeaturedTrackResourceTest.php` | 3 — index/create/edit smoke (auth via email `contact@snow-n-stuff.com` ca la celelalte resurse) |
| `tests/Feature/HomePageTest.php` | extins guardrail-ul „nimic nu dispare" cu seed `FeaturedTrack` + asserții pe „Now Spinning", titlu, artist |

### Bug fix colateral — `phpunit.xml`

Stage A a șters `tests/Unit/` complet, dar `phpunit.xml` păstra `<testsuite name="Unit"><directory>tests/Unit</directory></testsuite>` → `php artisan test` eșua cu `Test directory "tests/Unit" not found`. Eliminat referința; suite-ul Pest folosește doar `Feature`.

### Verificare Stage D

```
$ php artisan migrate --no-interaction
2026_05_09_180007_create_featured_tracks_table ... 425.81ms DONE

$ php artisan route:list --path=featured-tracks
GET|HEAD admin/featured-tracks            ListFeaturedTracks
GET|HEAD admin/featured-tracks/create     CreateFeaturedTrack
GET|HEAD admin/featured-tracks/{record}/edit  EditFeaturedTrack

$ php artisan test --compact
.......................................................
Tests:    55 passed (108 assertions)
Duration: ~13s

$ vendor/bin/pint --dirty --format agent
{"tool":"pint","result":"fixed","files":[ ... 2 fișiere reformatate (imports / FQN) ... ]}

$ npm run build
✓ 4 modules transformed, built in 3.99s
public/build/assets/app-*.css   25.31 + 77.10 kB
public/build/assets/app-*.js   142.47 kB
```

## Stage E — Design Refresh (branch `design/refresh`, post Stage D)

Cerința utilizator: *„crezi că putem îmbunătăți și design-ul aplicației?"* — discuție design urmată de 4 direcții confirmate (typo + logomark + motion + cards). Implementat fără să se piardă conținut sau funcționalitate.

### Decizii confirmate cu utilizatorul

| Decizie | Răspuns |
|---|---|
| Display font | Big Shoulders Display Black + Inter via Bunny Fonts (GDPR-safe Google Fonts proxy) |
| Logomark | Concept 2 — monogramă **SnS** într-un hexagon (interlocked S forms) |
| Cursor custom | **Skip** — cursor browser default păstrat |
| Card system scope | **D-light** — restyle cu datele existente, fără migrări noi |
| Strategie livrare | Branch separat `design/refresh` off `main` |

### A — Tipografie + ierarhie hero

`tailwind.config.js`:
- `sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans]`
- `display: ['"Big Shoulders Display"', 'Inter', ...defaultTheme.fontFamily.sans]`

`<x-partials.head>` — adăugat `<link rel="preconnect">` + `<link rel="stylesheet">` către `fonts.bunny.net` cu Big Shoulders Display 900 și Inter 400/500/600/700.

`livewire/hero.blade.php` rescris complet:
- **Eyebrow „Welcome to"** — `text-xs uppercase tracking-[0.4em] text-gray-500` (de la same-size cu brand-ul, devine accent discret)
- **Brand wordmark** — `font-display font-black uppercase` cu `font-size: clamp(3.5rem, 13vw, 9.5rem)`, gradient `from-red-700 via-red-500 to-red-300`, leading 0.82, tracking-tight. La 1920px brand-ul ocupă ~150px height vs ~80px înainte
- **Subtitle** — păstrat copy-ul exact (test guardrail), redus din `text-2xl md:text-3xl font-light` (subtitle prea greu) la `text-lg md:text-2xl font-light text-gray-200`
- **Genres** — consolidate într-un singur rând cu separatori `·` red-700/80 (înainte: paragraf prozaic „Snow 'n' Stuff is releasing Tech House, Deep House, House and Techno.")
- **Management** — eyebrow gradient cu rule horizontale + lista numelor cu separatori `·` (înainte: chips boxed `bg-white/5 rounded-md`, mai cluttered)
- **Curator pill** — păstrat ca element distinct, un singur stil cu border subtle
- **CTAs** — primary „Our Music" cu gradient red + shadow glow `0 8px 30px -8px rgba(220,38,38,0.6)` care intensifică la hover; secondary „Our Artists →" / „Blog & News →" devin text-only cu arrow care alunecă pe hover (înainte: bordered ghost buttons cu același weight ca primary)
- **Container bordat** (`bg-black/20 border-red-800/20`) eliminat — adăuga padding fără structură vizibilă
- **Scroll indicator** — pe desktop, în partea de jos absolută, „SCROLL" + linia gradient

### B — Logomark + design tokens + brand consistency

`resources/views/components/icons/logomark.blade.php` — SVG hexagon cu 2 forme S interlocked + dot central (~700 bytes). `viewBox="0 0 64 64"`, `currentColor`, `aria-hidden` default. Folosit în top-bar (cu `group-hover:rotate-180`) și footer.

`resources/views/components/brand-name.blade.php` — întoarce `Snow 'n' Stuff` (sau uppercase prin prop `:uppercase`). 6 locații normalizate (top-bar, footer ×2, about ×4 unde scria „Snow N Stuff" / „Snow `n` Stuff" cu backticks (!)).

`resources/css/app.css` extins cu un bloc complet de design tokens:
- Brand red channels (RGB triplets pentru `rgb(var(--xxx) / alpha)`)
- Surface borders/shadows tinted brand (`--sh-card`, `--sh-card-hover`, `--sh-cta-glow`, `--sh-cta-glow-hover`)
- Radii (`--r-card`, `--r-pill`, `--r-control`)
- Motion (`--ease-out-expo`, `--t-fast/base/slow`)
- `[data-reveal]` migrat la `var(--t-slow)` + `var(--ease-out-expo)`

`resources/views/components/section-header.blade.php` — eyebrow trecut la `text-red-500 tracking-[0.4em] font-semibold`; titlul migrat de la `text-4xl font-bold text-red-800` la `font-display font-black uppercase` cu clamp 2.5–4.5rem. Toate cele 5 secțiuni (Releases, Artists, Photo Gallery, Contact, Blog) împrumută noul stil instant fără edit per-secțiune.

### C — Motion + textură

`body::before` — overlay `<svg><filter feTurbulence baseFrequency="0.9"></svg>` inline ca data URI (~600 bytes), `mix-blend-mode: overlay`, `opacity: 0.05`. Suprimat la `prefers-reduced-motion`. Efectul: surfața devine tactilă, mai puțin „plastic", fără ca utilizatorul să-l observe conștient.

**Equalizer în pill-ul Featured Track** — 3 bare verticale `0.5×3` cu animații keyframe staggered (0.7s / 0.9s / 1.1s); apar lângă „NOW SPINNING". `aria-hidden="true"` (decorativ). Suprimate la `prefers-reduced-motion`.

**Parallax bg** — `data-hero-bg` translate3d cu 0.15× scrollY, prin `requestAnimationFrame` pentru smoothness. Skip pe touch (`pointer: coarse`) și `prefers-reduced-motion: reduce`. ~20 linii JS.

**CTA glow** (deja inclus în A) — primary CTA shadow gradient roșu care intensifică la hover.

### D-light — Stats strip + card restyle

`<x-stats-strip />` — secțiune nouă între Hero și About:
- 5 stat cards: Artists, Releases, Playlists (active), Genres (4 fix), Established (2020)
- Numere live din DB: `Artist::count()`, `Release::count()`, `Playlist::where('is_active', true)->count()`
- Padded la 2 caractere (01, 02, 23, 04) — Established afișat ca an plain (2020)
- **Count-up animat la scroll** via Alpine + IntersectionObserver: numerele sar de la 0 la real value cu easing cubic în 1.2 s când secțiunea intră în viewport
- Layout: 2 col mobile, 5 col desktop, gap responsive
- Numerele în `font-display font-black` cu gradient white→gray-500
- Eyebrow `text-red-500/80 tracking-[0.3em]`

Restyle card-uri (Artists + Releases) — minim invaziv:
- **Artists** — numărul de ordine 01/02/03 trecut la `font-display`, mai mare (clamp 4–6rem), opacitate roșu mai prezentă; titlul artistului `font-display uppercase` cu eyebrow „ARTIST" deasupra; hover lift de la `-4px` la `-6px` + border red-800/30 + shadow `var(--sh-card-hover)`
- **Releases** — adăugat header `Release` eyebrow + titlul (acum afișat — înainte `releases.title` exista în DB dar nu se randa nicăieri!); același hover lift + border + shadow ca la artists

### Tests

`HeroTest` actualizat — `assertSeeText('Snow n Stuff')` → `assertSeeText("Snow 'n' Stuff")` (brand-ul se randează acum cu apostrofuri canonice). Restul testelor neatinse.

```
Tests:    55 passed (108 assertions)
Duration: ~22 s (Bunny fonts request adaugă ~10 s la prima rulare; cached după)
```

`HomePageTest` păstrează intactă constrângerea „nimic nu dispare" — toate cele 11 secțiuni (10 originale + Stats Strip) randează la `/`.

### Verificare Stage E

```
$ php artisan view:clear && php artisan config:clear
$ php artisan test --compact
.......................................................
Tests:    55 passed (108 assertions)

$ vendor/bin/pint --dirty --format agent
{"tool":"pint","result":"passed"}

$ npm run build
public/build/assets/app-DikA6xf4.css   82.69 kB │ gzip: 13.10 kB
public/build/assets/app-N0MToBtp.js   142.83 kB │ gzip: 43.58 kB
✓ built in 6.64s
```

### Reversibilitate

Toate schimbările sunt pe branch separat `design/refresh`. Pentru a reveni instant la designul anterior:
```
git checkout main          # design refresh dispare
```
Pentru a o adopta:
```
git checkout main
git merge design/refresh
```

## Verificare end-to-end

```
$ php artisan route:list --except-vendor | wc -l
[18] routes  (identic cu baseline-ul: /, /blog, /blog/{slug}, /admin/* x 5 resurse)

$ npm run build
✓ 4 modules transformed.
public/build/manifest.json            0.33 kB
public/build/assets/app-D5cYTZrU.css 25.31 kB
public/build/assets/app-3IU6tmZZ.css 92.63 kB
public/build/assets/app-DRW01Jhp.js  142.47 kB
✓ built in 4.06s

$ vendor/bin/pint --dirty --format agent
{"tool":"pint","result":"passed"}

$ HTTP smoke: GET / → 200, 132 KB; GET /blog → 200, 62 KB
```

## Cerințe respectate

- ✅ **Zero conținut pierdut** — toate cele 10 secțiuni homepage + paginile blog + 5 resurse Filament inițiale rămân; testul `HomePageTest::it_renders_every_documented_homepage_section` blochează regresiile (acum verifică și pill-ul Featured Track când există un record activ).
- ✅ **Cerere utilizator livrată** — Featured Track în hero (sub CTAs) + resursă Filament dedicată cu cover image, URL Spotify, ordering, `is_active`. Stage D este complet, nu mai sunt items deferite.
- ✅ **Code cleanup** — 7 fișiere dead șterse (Stage A), 2 dependențe nefolosite eliminate, 1 dependență mutată corect, plus reziduul `phpunit.xml` din Stage A reparat în Stage D.
- ✅ **UI/Design** — design system extras (icons, section-header, layouts), focus-visible peste tot, animații scroll-triggered, optimizare imagini, branding Filament; pill-ul nou reutilizează `<x-icons.spotify>`, focus ring și politica de imagini deja stabilite.
- ✅ **Graceful degradation** — fără track activ, hero-ul randează identic cu starea pre-D; cu track, pill-ul apare ca o nouă etapă de animație **după** CTAs (gradientul „Snow n Stuff" rămâne signature).
- ✅ **Toate punctele de vedere** — code structure (scopes + accessors în loc de logică în view), performance (composite index `(is_active, order)`, FileUpload pe disk public, fără iframe extern în pill), a11y (`aria-label` descriptiv pe CTA, alt-text pe cover, focus ring), maintainability (mirror exact al patternului `ReleaseResource`/`PlaylistResource`), correctness (URL parsing testat, ordering testat, bug fix `phpunit.xml`), testing (55 teste, 108 assertions — +8 cazuri în Stage D).

## Branch & commits

```
$ git log --oneline --all
[design/refresh]   <pending>  feat: design refresh — display font, logomark, motion, stats strip (Stage E)
[main]    e1d010f  chore: gitignore .claude/settings.local.json
[main]    57ec6b7  featured track (Stage D)
[main]    b1ef728  docs: add IMPLEMENTATION.md summarising Stages A-C
[main]    7ca58c8  test: add Pest coverage for routes, Livewire components, Filament resources (Stage C)
[main]    5b28881  refactor: shared layout, reusable icons/components, a11y, image perf, Filament polish (Stage B)
[main]    8ac3ecc  chore: remove dead code and migrate Fancybox to local bundle (Stage A)
```

Stage E este pe branch-ul `design/refresh`, gata pentru commit + merge către `main`. Pentru a adopta:

```
git checkout design/refresh && git add -A && git commit -m "feat: design refresh (Stage E)"
git checkout main && git merge design/refresh
```

## Redesign 2026 (etapele 1–7)

Site-ul public a fost refăcut complet pe branch-ul `redesign`, pornit din `main` la `fb3dd9d`: catalog structurat, pagini noi, SEO și infrastructură pentru agenți AI, player persistent, formular de demo și consimțământ pentru cookie-uri. Merge-ul în `main` și deploy-ul se fac separat, după runbook-ul de mai jos (`deploy.sh` face `git pull origin main`).

### Etape și commit-uri

| Etapa | Commit | Pe scurt | Teste |
|---|---|---|---|
| — | `dc76605` | Ghidurile Laravel Boost și skill-urile regenerate de `boost:update` | — |
| 1 | `622450d` | Model de date pentru catalog, admin refăcut, migrarea datelor vechi | 147 |
| 2a | `4e8667f` | SEO și infrastructură pentru agenți AI | 179 |
| 2b | `c9cdc69` | Layout nou și paginile de catalog | 260 |
| 3 | `3b868c8` | Homepage nou, blog restilizat, designul vechi eliminat | 289 |
| 4 | `1397397` | Player persistent în bara de jos (embed Spotify) | 324 |
| 5 | `b697e72` | Formular de demo, consimțământ pentru cookie-uri, pagina de confidențialitate | 390 |
| 6 | (necomisă la scrierea acestei secțiuni) | Performanță, curățenie, pregătire de lansare | 406 |
| 7 | — | Raportul PDF pentru client (în engleză): modificările, capturi înainte/după, măsurătorile din etapa 6, ghidul adminului și lista de după lansare. Merge-ul în `main` și deploy-ul le face echipa, după runbook | — |

### Etapa 1 — Catalog structurat, admin și migrarea datelor vechi (`622450d`)

- **Lansări:** slug, dată de lansare, format și gen (enum-uri), credit afișat (`artist_display`), link Spotify, smartlink, copertă, `is_featured`, suport DJ și poziții în topuri. Tabel nou `tracks` și pivot `artist_release`.
- **Artiști:** slug, fotografie, rol, origine, profiluri sociale, highlights, press kit.
- **Date vechi:** codul de embed Spotify lipit în admin devine un `spotify_url` canonic (lansări, playlisturi); `featured_tracks` se mută în `releases.is_featured`; coloanele vechi se șterg doar după conversie, iar rollback-ul reconstruiește iframe-urile. Resursa FeaturedTrack a dispărut.
- **Filament:** formulare noi pentru Release/Artist/Playlist (repeater de piese, durate m:ss, validarea linkurilor Spotify).
- **`catalog:backfill`** completează cele 19 lansări existente din `database/data/catalog.json` (doar câmpurile goale; titlurile doar cât timp sunt neschimbate). **`SnapshotSeeder`** recreează local site-ul public (doar în afara producției).

### Etapa 2a — SEO și infrastructură pentru agenți AI (`4e8667f`)

- `SeoData` + `<x-seo>`: titlu, descriere, canonical din `APP_URL`, Open Graph/Twitter, link către versiunea Markdown și `llms.txt`, verificare Google/Bing opțională.
- JSON-LD `@graph` (`App\Support\Seo\Schema`): Organization, WebSite, BreadcrumbList, BlogPosting.
- `robots.txt`, `sitemap.xml` și `llms.txt` dinamice, în cache-ul separat `pages`, golit de `PublicContentObserver`. `robots.txt` permite boții de căutare și de răspuns, blochează crawlerele de antrenare și **blochează tot în afara producției**.
- `spatie/laravel-markdown-response` pe grupul `web`: orice pagină publică are o versiune `.md` (și prin `Accept: text/markdown`).
- IndexNow: cheie derivată din `APP_KEY`, trimiteri grupate, doar în producție.
- Au dispărut `robots.txt` static și comanda `sitemap:generate`; `deploy.sh` șterge `public/sitemap.xml` rămas și golește cache-ul `pages`.

### Etapa 2b — Layout nou și paginile de catalog (`c9cdc69`)

- Layout în designul aprobat: tokenuri reci, închise (`tailwind.config.js`), fonturile Schibsted Grotesk, Martian Mono și Big Shoulders Display (Bunny Fonts), structură semantică header/nav/main/footer.
- Pagini: `/releases` (catalog Livewire cu filtre după artist, format, gen și an), `/releases/{slug}`, `/artists`, `/artists/{slug}`, `/playlists`, `/about` și un 404 în layoutul nou.
- JSON-LD MusicAlbum/MusicRelease/MusicRecording, MusicGroup, MusicPlaylist; sitemap, `llms.txt` și IndexNow acoperă paginile noi.
- Tailwind nu mai scanează view-urile compilate, deci build-ul e determinist.

### Etapa 3 — Homepage nou, blog restilizat, designul vechi eliminat (`3b868c8`)

- Homepage: hero cu lansarea promovată (vinil la hover), cele mai noi 8 lansări, „Supported & played by” din câmpurile de suport și topuri, artiști, playlisturi, about scurt cu fotografii, noutăți (lansări și postări), linkuri de urmărire. Fără iframe-uri, un singur `<h1>`.
- Blog (News) pe layoutul nou; HTML-ul postărilor e curățat la randare (`App\Support\ArticleHtml`).
- Au fost șterse layouturile vechi, preloader-ul, back-to-top, top bar-ul, stats strip, secțiunile și componentele vechi de homepage, CSS/JS-ul vechi și imaginile nefolosite.

### Etapa 4 — Player persistent în bara de jos (`1397397`)

- Navigare SPA cu `wire:navigate` pe linkurile interne, ca muzica să nu se oprească între pagini.
- Embedul Spotify real, vizibil (iFrame API, 152 px), cu titlul piesei, coadă anterior/următor și închidere; butoane Play pe cardurile de lansări, playlisturi și noutăți, în hero-uri și pe paginile de artist.
- La primul Play se cere acordul (Spotify setează cookie-uri); până atunci nu se încarcă nimic de la Spotify.

### Etapa 5 — Demo, consimțământ, confidențialitate (`b697e72`)

- `/demos`: formular Livewire (link privat SoundCloud/Dropbox/Drive, gen, țară, mesaj, confirmarea drepturilor) cu honeypot, timp minim de completare și rate limiting; notificare pe e-mail la `DEMO_NOTIFY_EMAIL`.
- Filament `DemoSubmissionResource`: statusuri (new/listened/accepted/declined), notițe, filtre, badge în meniu. Demo-urile neacceptate se șterg după 12 luni (`model:prune`, programat zilnic).
- Banner de consimțământ: Accept all / Reject all / Customize, categoriile analytics și media externă, alegerea ține 6 luni, „Cookie settings” în footer. Google Analytics se încarcă doar în producție și doar după acord (basic consent mode). Playerele din postări sunt placeholder-e până la acord.
- `/privacy`: draft GDPR (art. 13), în engleză, cu tabelul de cookie-uri; avizul de draft apare doar în afara producției.

### Etapa 6 — Performanță, curățenie, pregătire de lansare

- **Coperțile Spotify în paralel:** `SpotifyThumbnail::warm()` cere într-o singură rundă `Http::pool` (toate deodată, `connectTimeout(2)`, `timeout(3)`) doar thumbnail-urile care nu sunt încă în cache, apoi `urlFor()` doar citește cache-ul. `warmArtwork()` primește modelele unei pagini și le trimite doar pe cele fără imagine urcată (`HasSpotifyArtwork::artworkSpotifyUrl()`). Se apelează înainte de randare în `HomeController` (hero, cele 8 lansări, noutăți, artiști, playlisturi), `ReleaseCatalogue`, `ReleaseController@show`, `ArtistController` (index și show) și `PlaylistsController`. `AboutController` nu afișează coperți. Sitemap-ul rămâne doar pe cache (`cachedUrlFor`).
- **Expirare eșalonată:** un thumbnail găsit rămâne în cache 30 de zile plus 0–5 zile aleatoriu, ca să nu expire toate în aceeași zi. Fiecare succes se păstrează și ca „ultimul URL bun” (maximum 180 de zile, limita pentru cache temporar din termenii Spotify). La un eșec (eroare, timeout, conexiune), dacă există un URL bun anterior, acesta se folosește încă o zi; altfel eșecul rămâne în cache 10 minute.
- **Header-e de securitate** (`App\Http\Middleware\SecurityHeaders`, pe grupul `web`, înaintea răspunsurilor Markdown, și pe panoul Filament): `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `X-Frame-Options: SAMEORIGIN`, `Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()`. **Fără CSP** deocamdată: ar rupe iframe-urile Spotify și Beatport, Livewire și Alpine fără o configurare atentă.
- **Curățenie:** listener-ul `photo-added` din `resources/js/app.js` (evenimentul nu mai era emis nicăieri); importuri nefolosite în paginile Filament `Create*` și formatare în `User` (Pint trece acum pe tot `app/`); `config/livewire.php` indica layoutul șters `components.layouts.app`, acum `components.layouts.site`.
- **Accesibilitate (din Lighthouse):** tokenul `dim` trece de la `#626D78` la `#7D8894` (contrast 5,4:1 pe `ink`, peste 4,7:1 pe `slab2`, față de 3,7:1 înainte); numărul de lansări din chip-urile de artist nu mai e estompat cu `opacity-70`.

**Măsurători (26.09.2026):**

| | Înainte | După |
|---|---|---|
| Homepage cu cache-ul de thumbnail-uri gol (16 cereri oEmbed, local) | 4,4–5,1 s | median 0,8 s (0,5–1,4 s; ~1 din 5 încărcări 2–3 s, când un răspuns Spotify întârzie) |
| Homepage cu cache-ul cald | 0,04–0,06 s | 0,04–0,06 s |
| Lighthouse mobil, homepage (Perf / A11y / LCP / TBT) | 63 / 94 / 6,5 s / 240 ms | 80 / 100 / 4,2 s / 120 ms |
| Lighthouse mobil, /blog (Perf / A11y / LCP) | 62 / 96 / 33,5 s | 85 / 100 / 4,0 s |
| Lighthouse desktop, homepage (Perf / A11y / greutate / cereri) | 92 / 94 / 1,86 MB / 227 | 96 / 100 / 0,91 MB / 34 |

„Înainte” e site-ul vechi, live; „după” e branch-ul `redesign` servit local de Herd (HTTP simplu, config cu `APP_DEBUG=false`). Local, Best Practices (79) pică doar pe HTTPS, iar SEO (66–69) doar pe `robots.txt`, care blochează tot în afara producției; în producție ambele ar trebui să fie 100. Rămân de făcut, ca optimizări: imaginile de copertă ale postărilor (~13 MB pe /blog, încărcate la dimensiunea originală), fonturile servite local în loc de Bunny Fonts (CSS care blochează randarea), Fancybox încărcat doar pe /about.

### Decizii importante

- **Fără Spotify Web API.** API-ul cere credențiale, token-uri și are limite. Datele catalogului sunt îngrijite în `database/data/catalog.json` și în admin; coperta e cea urcată în admin sau thumbnail-ul public oEmbed (300 px, fără cheie), ținut în cache. Pentru o copertă mai mare pe pagina lansării, se urcă imaginea în admin.
- **Playerul stă în afara `<body>`.** Livewire 3.8 cu `@persist` mută elementul la navigare, dar reîncarcă iframe-ul, deci muzica s-ar opri. `resources/js/player.js` creează bara după `<body>`, pe care `wire:navigate` nu o înlocuiește, și șterge copiile din snapshot-urile back/forward.
- **Consimțământ înainte de orice terț.** Nimic din ce cere acord (Google Analytics, embedurile Spotify/Beatport/nfan.link) nu se încarcă înainte de alegere. Alegerea stă în `localStorage` 6 luni și se schimbă din „Cookie settings”. GA doar în producție.
- **Indexarea depinde de `APP_ENV`.** În afara producției `robots.txt` blochează tot, iar canonical-urile vin din `APP_URL`.
- **`public/build` e comis.** Serverul nu are Node; după orice schimbare de CSS/JS se rulează `npm run build` și se comite rezultatul.

### Runbook de producție

1. **Backup:** baza de date și `storage/app/public` (migrările din etapa 1 convertesc și apoi șterg coloanele vechi de embed).
2. **Pe server:** `git status` (fără modificări locale neașteptate) și `crontab -l` (se scoate vechiul `sitemap:generate`; comanda nu mai există).
3. **`.env`:**
   - `APP_ENV=production` (altfel `robots.txt` blochează indexarea);
   - `APP_URL=https://snow-n-stuff.com` (fără `www`);
   - `MAIL_*` funcțional, pentru notificările de demo;
   - opțional: `DEMO_NOTIFY_EMAIL`, `GOOGLE_ANALYTICS_ID`, `PRIVACY_CONTROLLER_NAME` / `PRIVACY_CONTROLLER_ADDRESS` / `PRIVACY_CONTACT_EMAIL`, `GOOGLE_SITE_VERIFICATION` / `BING_SITE_VERIFICATION`.
4. **Deploy** cu `deploy.sh`, după merge-ul în `main`: `git pull`, șterge `public/sitemap.xml` rămas de la vechiul generator, `composer install --no-dev`, `migrate --force`, `config:cache`, golește cache-ul `pages`, `route:cache`, `view:cache`.
5. **Catalogul:** `php artisan catalog:backfill --dry-run`, se verifică lista, apoi `php artisan catalog:backfill`.
6. **Opțional, cron:** `* * * * * cd ~/domains/snow-n-stuff.com/public_html && /opt/alt/php84/usr/bin/php artisan schedule:run >> /dev/null 2>&1`, pentru `model:prune` (demo-urile neacceptate, după 12 luni).
7. **Verificări cu `curl`:**
   - `curl -s https://snow-n-stuff.com/robots.txt` (fără `Disallow: /` general);
   - `curl -s https://snow-n-stuff.com/sitemap.xml | head`;
   - `curl -s https://snow-n-stuff.com/llms.txt | head`;
   - o pagină Markdown: `curl -s https://snow-n-stuff.com/releases/speak-to-me.md | head`;
   - fișierul cheii IndexNow: adresa o dă `php artisan tinker --execute 'echo App\Support\Seo\IndexNow::keyLocation();'`, apoi `curl -s <adresa>`;
   - header-ele de securitate: `curl -sI https://snow-n-stuff.com/`.
8. **Search Console și Bing Webmaster Tools:** se retrimite `https://snow-n-stuff.com/sitemap.xml`.
9. **Rich Results Test** (Google) pe o lansare, un artist și o postare.
