<?php

namespace App\Http\Controllers;

use App\Support\Seo\Schema;
use App\Support\Seo\SeoData;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class PrivacyController extends Controller
{
    public const DESCRIPTION = "How Snow 'n' Stuff handles personal data: analytics and external players only with your consent, demos sent through the form, your rights and the cookies used on this site.";

    /**
     * The date of this version of the policy, shown on the page.
     */
    public const UPDATED_AT = '2026-09-26';

    /**
     * The privacy policy and the list of cookies.
     */
    public function __invoke(): View
    {
        $path = route('privacy', absolute: false);
        $trail = ['/' => 'Home', $path => 'Privacy & cookies'];

        return view('privacy', [
            'trail' => $trail,
            'controller' => config('site.privacy'),
            'updatedAt' => CarbonImmutable::parse(self::UPDATED_AT),
            'sessionCookie' => (string) config('session.cookie'),
            // Google Analytics 4 names its second cookie after the measurement id, without "G-".
            'analyticsContainerId' => Str::after((string) config('services.google_analytics.id'), 'G-') ?: '<ID>',
            'sessionDuration' => config('session.expire_on_close')
                ? 'Until you close the browser'
                : CarbonInterval::minutes((int) config('session.lifetime'))->cascade()->forHumans(),
            'seo' => new SeoData(
                title: 'Privacy & cookies - '.SeoData::SITE_NAME,
                description: self::DESCRIPTION,
                path: $path,
                schema: [Schema::breadcrumbs($trail)],
            ),
        ]);
    }
}
