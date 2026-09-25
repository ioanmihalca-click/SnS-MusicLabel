<?php

namespace App\Http\Controllers;

use App\Support\Seo\Schema;
use App\Support\Seo\SeoData;
use Illuminate\Contracts\View\View;

class AboutController extends Controller
{
    public const DESCRIPTION = "About Snow 'n' Stuff: music management, label and production for Tech House, Deep House, House and Techno, based in Stockholm and Romania. Photos and contacts.";

    /**
     * The label's story, the photo gallery and the contacts.
     */
    public function __invoke(): View
    {
        $path = route('about', absolute: false);
        $trail = ['/' => 'Home', $path => 'About'];

        return view('about', [
            'trail' => $trail,
            'seo' => new SeoData(
                title: 'About - '.SeoData::SITE_NAME,
                description: self::DESCRIPTION,
                path: $path,
                schema: [Schema::breadcrumbs($trail)],
            ),
        ]);
    }
}
