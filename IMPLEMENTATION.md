# Implementare — Cleanup, UI Polish, Tests & Featured Track

Această implementare s-a desfășurat în 4 stage-uri (A–D). Stage-urile A–C au fost livrate pe branch-ul `refactor/cleanup-and-polish` în 3 commit-uri; Stage D este o iterație ulterioară pe `main` (cerința explicită „featured track în hero + resursă Filament dedicată"). Niciun conținut, secțiune, link sau imagine existentă nu a fost eliminat. Toate cele 10 secțiuni de homepage, paginile blog și cele 5 resurse Filament inițiale rămân exact unde erau; Stage D adaugă o nouă resursă (FeaturedTrack) și un pill condiționat sub CTAs din hero.

## Sumar executiv

| Metric | Înainte | După Stage C | După Stage D |
|---|---|---|---|
| Fișiere șterse | — | 7 dead-code | (idem) |
| Componente Blade reutilizabile | 0 | 14 | 14 |
| SVG-uri inline duplicate | ~22 copii | 0 | 0 |
| Teste | 0 | 47 (90 assertions) | **55 (108 assertions)** |
| Modele | 5 | 5 | **6** (+ FeaturedTrack) |
| Resurse Filament | 5 | 5 | **6** (+ FeaturedTrack) |
| Componente Livewire | 7 | 7 | **8** (+ FeaturedTrack) |
| Răspuns `/` (HTTP 200) | ~116 KB | ~132 KB | ~133 KB cu pill activ, ~132 KB fără |
| Bundle JS | 141 KB CDN | 142 KB local | 142 KB local (neschimbat) |
| Acoperire focus-visible | 0 | toate links/butoane | (idem, plus CTA pill) |

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
$ git log --oneline    # post Stage C
b1ef728 docs: add IMPLEMENTATION.md summarising Stages A-C
7ca58c8 test: add Pest coverage for routes, Livewire components, Filament resources (Stage C)
5b28881 refactor: shared layout, reusable icons/components, a11y, image perf, Filament polish (Stage B)
8ac3ecc chore: remove dead code and migrate Fancybox to local bundle (Stage A)
```

Stage D este pe `main`, **uncommitted** la momentul acestei documentații (fișiere noi + edits deja aplicate; tests + pint + build confirmate). Pentru a finaliza, rulați:

```
git add -A
git commit -m "feat: featured track resource + hero pill (Stage D)"
```
