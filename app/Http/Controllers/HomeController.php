<?php

namespace App\Http\Controllers;

use App\Support\Seo\SeoData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * The homepage. It is also routed as `/index` so that its Markdown version
     * lives at `/index.md`; any other request for `/index` goes to `/`.
     */
    public function __invoke(Request $request): View|RedirectResponse
    {
        if ($request->routeIs('home.index') && ! $this->isMarkdownSuffixRequest($request)) {
            return to_route('home', status: 301);
        }

        return view('welcome', [
            'seo' => new SeoData(path: '/'),
        ]);
    }

    /**
     * Whether spatie/laravel-markdown-response stripped a `.md` suffix from the URL.
     */
    private function isMarkdownSuffixRequest(Request $request): bool
    {
        return (bool) $request->attributes->get('markdown-response.suffix');
    }
}
