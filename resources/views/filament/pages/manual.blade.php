{{--
    The admin manual (App\Filament\Pages\Manual). Filament's CSS has no prose
    styles, so lists, paragraphs and links get a few rules scoped to
    .admin-manual; colours come from the panel's theme variables, light and dark.
--}}
<x-filament-panels::page>
    <style>
        .admin-manual { display: grid; gap: 1.5rem; font-size: .875rem; line-height: 1.6; }
        .admin-manual p, .admin-manual ul, .admin-manual ol, .admin-manual table { margin: 0; }
        .admin-manual p + p, .admin-manual p + ul, .admin-manual p + ol, .admin-manual ul + p, .admin-manual ol + p,
        .admin-manual p + table, .admin-manual table + p { margin-top: .75rem; }
        .admin-manual ul { list-style: disc; padding-inline-start: 1.25rem; }
        .admin-manual ol { list-style: decimal; padding-inline-start: 1.25rem; }
        .admin-manual li + li { margin-top: .375rem; }
        .admin-manual li > ul { margin-top: .375rem; }
        .admin-manual h3 { margin: 1.5rem 0 .5rem; font-size: .9375rem; font-weight: 600; }
        .admin-manual h3:first-child { margin-top: 0; }
        .admin-manual strong { font-weight: 600; }
        .admin-manual a { color: rgb(var(--primary-600)); text-decoration: underline; text-underline-offset: 2px; }
        .dark .admin-manual a { color: rgb(var(--primary-400)); }
        .admin-manual code { padding: .0625rem .3125rem; border-radius: .25rem; font-size: .8125rem; background: rgb(var(--gray-100)); }
        .dark .admin-manual code { background: rgb(var(--gray-800)); }
        .admin-manual .admin-manual-note { color: rgb(var(--gray-500)); }
        .dark .admin-manual .admin-manual-note { color: rgb(var(--gray-400)); }
        .admin-manual table { width: 100%; border-collapse: collapse; }
        .admin-manual th, .admin-manual td { padding: .375rem .75rem .375rem 0; text-align: start; vertical-align: top; border-bottom: 1px solid rgb(var(--gray-200)); }
        .dark .admin-manual th, .dark .admin-manual td { border-color: rgb(var(--gray-700)); }
        .admin-manual th { font-weight: 600; }
    </style>

    <div class="admin-manual">
        <x-filament::section
            icon="heroicon-o-information-circle"
            heading="Overview"
            description="What is in the admin and what the site does by itself."
        >
            <h3>In the menu</h3>
            <ul>
                <li><strong>Catalogue:</strong> Artists (the roster), Releases and Playlists.</li>
                <li><strong>Content:</strong> Blogs, the news posts shown on the homepage and at /blog.</li>
                <li><strong>Label:</strong> Demos sent by artists through the form at /demos.</li>
                <li><strong>Media:</strong> Photos for the About page gallery and the homepage.</li>
                <li><strong>Help:</strong> this manual.</li>
            </ul>

            <h3>What happens automatically</h3>
            <ul>
                <li>Whenever you save or delete a release, track, artist, playlist, photo or post, the sitemap (sitemap.xml), llms.txt and the Markdown versions of the pages are rebuilt. Changes that save nothing (a scheduled post going live, rows dragged into a new order) show up there within an hour.</li>
                <li>On the live site, Bing and the other search engines that use IndexNow are told right away which pages changed. Google finds the changes through the sitemap.</li>
                <li>Share images (the picture shown when a link is posted on social media or in a chat) come from the release cover, the post's cover image and the artist photo (Spotify's image for releases and artists without an upload). Other pages use the label's default image.</li>
                <li>The homepage builds itself from your data: the hero shows the featured release (or else the newest), "Supported &amp; played by" lists the DJ support and chart positions from the releases, and the news mixes the latest releases that are out with the latest published posts.</li>
                <li>Every public page also exists as plain text (Markdown) for AI assistants, e.g. /releases.md, and llms.txt describes the site for them.</li>
                <li>A cookie banner asks visitors before Google Analytics or any external player loads (see Privacy &amp; cookies below).</li>
            </ul>
        </x-filament::section>

        <x-filament::section
            icon="heroicon-o-musical-note"
            heading="Releases"
            description="Catalogue → Releases → New release."
            collapsible
            collapsed
        >
            <ol>
                <li><strong>Title:</strong> the release title only, without artist names.</li>
                <li><strong>Credit:</strong> the full credit as shown on the site, guests included, e.g. "THK &amp; Pacha Man". Leave it empty to use the artists chosen below.</li>
                <li><strong>Artists:</strong> the roster artists on the release. Their artist pages list it.</li>
                <li><strong>Format</strong> (Single, EP, Album, Compilation), <strong>Genre</strong> and <strong>Release date</strong>. A release enters the homepage news on its release date.</li>
                <li><strong>Listen:</strong> the <strong>Spotify URL</strong> (the album or track link, from "Share" → "Copy link") and/or the <strong>Smartlink</strong> from the distributor (one link to every platform, which also works for pre-save). At least one is required, so an upcoming release can be saved with only the smartlink and get its Spotify link on release day.</li>
                <li><strong>Cover:</strong> upload the original artwork. It is cropped to a square and resized to 1200×1200. Without an uploaded cover the site shows Spotify's small thumbnail, so always upload one.</li>
                <li><strong>Tracks:</strong> "Add track" for each one, with the <strong>Title</strong>, the <strong>Version</strong> (e.g. Extended Mix), the <strong>Duration</strong> as minutes:seconds (e.g. 3:45) and the <strong>ISRC</strong>. Drag the tracks into order.</li>
                <li><strong>Promotion:</strong>
                    <ul>
                        <li><strong>Featured</strong> puts the release in the homepage hero. If several releases are featured, the newest one wins; with none featured, the newest release is shown.</li>
                        <li><strong>DJ support:</strong> type a DJ's name and press Enter. The names appear on the release page and in "Supported &amp; played by" on the homepage.</li>
                        <li><strong>Chart position</strong> (e.g. #59) and <strong>Chart name</strong> (e.g. Beatport Hype).</li>
                    </ul>
                </li>
                <li><strong>Description:</strong> optional text for the release page.</li>
                <li><strong>Advanced → Slug:</strong> the end of the page's address. Leave it empty to generate it from the title. Changing it changes the public URL.</li>
            </ol>
        </x-filament::section>

        <x-filament::section
            icon="heroicon-o-user-group"
            heading="Artists"
            description="Catalogue → Artists."
            collapsible
            collapsed
        >
            <ul>
                <li><strong>Name</strong>, <strong>Role</strong> (e.g. DJ / producer duo) and <strong>Origin</strong> (e.g. Stockholm, Sweden).</li>
                <li><strong>Order:</strong> lower numbers appear first. You can also drag the rows in the Artists list: click the reorder button above the table.</li>
                <li><strong>Photo:</strong> a portrait, cropped to 4:5 and resized to 1200×1500. The site shows it in black and white.</li>
                <li><strong>Links:</strong> Spotify (required), Instagram, SoundCloud and Beatport.</li>
                <li><strong>Highlights:</strong> short facts for the artist page, e.g. charts, key DJ support or airplay.</li>
                <li><strong>Press kit:</strong> a PDF up to 10 MB, offered for download on the artist page.</li>
                <li><strong>Description:</strong> the bio (required).</li>
                <li><strong>Advanced → Slug:</strong> as for releases, changing it changes the public URL.</li>
            </ul>
        </x-filament::section>

        <x-filament::section
            icon="heroicon-o-rectangle-stack"
            heading="Playlists"
            description="Catalogue → Playlists."
            collapsible
            collapsed
        >
            <ul>
                <li><strong>Spotify URL:</strong> the playlist link from Spotify ("Share" → "Copy link").</li>
                <li><strong>Title</strong> and <strong>Tags</strong>, separated with " · ", e.g. Melodic Techno · Ibiza · Afro House.</li>
                <li><strong>Cover:</strong> optional, square, resized to 1200×1200. Without one, Spotify's playlist image is used.</li>
                <li><strong>Is active:</strong> switch it off to hide the playlist from the site without deleting it.</li>
                <li><strong>Order:</strong> lower numbers appear first, or drag the rows in the Playlists list with the reorder button.</li>
            </ul>
        </x-filament::section>

        <x-filament::section
            icon="heroicon-o-document-text"
            heading="News (Blogs)"
            description="Content → Blogs: writing, publishing and the homepage hero line."
            collapsible
            collapsed
        >
            <h3>Writing a post</h3>
            <ul>
                <li><strong>Title</strong> and <strong>Content</strong>, written in the editor.</li>
                <li><strong>Spotify player:</strong> paste the Spotify link ("Share" → "Copy link") alone on its own line; on the site it becomes a player. Do not paste player (embed) code: the editor removes it on save. Older posts that still contain embedded players show an "Embedded players" warning at the top, because saving them removes the players: replace them with plain Spotify links first.</li>
                <li><strong>Cover image:</strong> cropped and resized to 1200×630. It is also the post's share image.</li>
                <li><strong>SEO</strong> (optional): Meta title (50–60 characters) and Meta description (150–160 characters). Without them, the post's title and the start of its text are used.</li>
            </ul>

            <h3>Publishing</h3>
            <ul>
                <li><strong>Draft:</strong> leave "Publish Date" empty. The post stays hidden from the site.</li>
                <li><strong>Publish now:</strong> the button next to "Create" on a new post, the button at the top of the edit page (it saves your changes too) or the "Publish now" action in the post's row in the Blogs list.</li>
                <li><strong>Schedule:</strong> set "Publish Date" in the future; the post goes live on its own at that time. The clock button next to the field fills in the current date and time.</li>
                <li><strong>Unpublish:</strong> in the post's row or at the top of the edit page, after a confirmation. The post goes back to draft.</li>
                <li>For several posts at once, tick their rows and use the bulk actions "Publish now" or "Unpublish".</li>
                <li>The list's filters show Published only, Scheduled or Drafts; the "Live" column shows what is on the site now.</li>
            </ul>

            <h3>Homepage hero line</h3>
            <p>To point visitors to a post for a while, e.g. an event like ADE, open the post's <strong>Homepage hero</strong> section:</p>
            <ul>
                <li><strong>Show in homepage hero until:</strong> the date and time the line disappears, on its own. Leave it empty to keep the post out of the hero.</li>
                <li><strong>Hero text:</strong> a short line of up to 80 characters, e.g. "Meet us at ADE 2026 · Amsterdam, 21–25 October". Empty uses the post title.</li>
            </ul>
            <p>The line appears at the top of the homepage hero and links to the post. Only published posts are shown; if several are active, the most recently published one wins. The Blogs list has an "In hero" column, which you can show or hide from the table's column menu.</p>
        </x-filament::section>

        <x-filament::section
            icon="heroicon-o-camera"
            heading="Photos"
            description="Media → Photos."
            collapsible
            collapsed
        >
            <ul>
                <li>Upload an <strong>Image</strong> and give it a <strong>Title</strong>, which describes the photo for screen readers and appears as its caption in the gallery.</li>
                <li>The six newest photos appear on the homepage; all of them are in the gallery on the About page, newest first.</li>
            </ul>
        </x-filament::section>

        <x-filament::section
            icon="heroicon-o-inbox-arrow-down"
            heading="Demos"
            description="Label → Demos: the demos artists send through the form at /demos."
            collapsible
            collapsed
        >
            <ul>
                <li>Demos only come from the public form; there is no "New" button. The number next to Demos in the menu counts the demos with status New.</li>
                <li>Click <strong>Review</strong> to open one. What the artist sent cannot be edited; the private link opens in a new tab.</li>
                <li>Set the <strong>Status</strong>: New, Listened, Accepted or Declined. <strong>Notes</strong> are only visible in the admin.</li>
                <li>Each new demo is also e-mailed to the address set on the server (<code>DEMO_NOTIFY_EMAIL</code>). Ask the developer to change it.</li>
                <li>Demos that were not accepted are deleted 12 months after they were sent. This only happens if the server runs Laravel's scheduled tasks (a cron job); accepted demos are kept.</li>
            </ul>
        </x-filament::section>

        <x-filament::section
            icon="heroicon-o-shield-check"
            heading="Privacy & cookies"
            collapsible
            collapsed
        >
            <ul>
                <li>The cookie banner offers Accept all, Reject all and Customize. <strong>Necessary</strong> cookies are always on; visitors choose <strong>Analytics</strong> (Google Analytics, only active when an Analytics ID is set on the server) and <strong>External media</strong> (players from Spotify, Beatport and nfan.link). Nothing loads before they choose; the banner asks again after six months, and "Cookie settings" in the footer reopens it at any time.</li>
                <li>The text of the privacy page (<a href="{{ route('privacy') }}" target="_blank" rel="noopener">/privacy</a>) must be reviewed by the label; changes to it are made in the code by the developer.</li>
                <li>The company details on that page (name, address, contact e-mail) are set on the server: <code>PRIVACY_CONTROLLER_NAME</code>, <code>PRIVACY_CONTROLLER_ADDRESS</code> and <code>PRIVACY_CONTACT_EMAIL</code>. The address line is left out while it is empty.</li>
            </ul>
        </x-filament::section>

        <x-filament::section
            icon="heroicon-o-light-bulb"
            heading="Tips"
            collapsible
            collapsed
        >
            <h3>Image sizes</h3>
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Shape</th>
                        <th>Saved at</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Release cover</td>
                        <td>Square</td>
                        <td>1200×1200</td>
                    </tr>
                    <tr>
                        <td>Playlist cover</td>
                        <td>Square</td>
                        <td>1200×1200</td>
                    </tr>
                    <tr>
                        <td>Artist photo</td>
                        <td>Portrait 4:5</td>
                        <td>1200×1500</td>
                    </tr>
                    <tr>
                        <td>Post cover image</td>
                        <td>Landscape 1200:630</td>
                        <td>1200×630</td>
                    </tr>
                    <tr>
                        <td>Photo</td>
                        <td>Any</td>
                        <td>As uploaded</td>
                    </tr>
                </tbody>
            </table>
            <p class="admin-manual-note">Upload images at least that large so they stay sharp.</p>

            <h3>Keep titles clean</h3>
            <ul>
                <li>No artist names in release titles: they belong in Credit and Artists.</li>
                <li>Track versions (Extended Mix, Radio Edit) go in the Version field, not in the track title.</li>
                <li>Avoid changing a slug once a page is public: links shared elsewhere point to the old address.</li>
            </ul>

            <h3>The public pages</h3>
            <ul>
                <li><a href="{{ route('releases.index') }}" target="_blank" rel="noopener">/releases</a></li>
                <li><a href="{{ route('artists.index') }}" target="_blank" rel="noopener">/artists</a></li>
                <li><a href="{{ route('playlists.index') }}" target="_blank" rel="noopener">/playlists</a></li>
                <li><a href="{{ route('blog.index') }}" target="_blank" rel="noopener">/blog</a></li>
                <li><a href="{{ route('demos') }}" target="_blank" rel="noopener">/demos</a></li>
                <li><a href="{{ route('privacy') }}" target="_blank" rel="noopener">/privacy</a></li>
            </ul>
        </x-filament::section>
    </div>
</x-filament-panels::page>
