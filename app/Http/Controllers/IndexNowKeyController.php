<?php

namespace App\Http\Controllers;

use App\Support\Seo\IndexNow;
use Illuminate\Http\Response;

class IndexNowKeyController extends Controller
{
    /**
     * The IndexNow key file: `/{key}.txt` containing exactly the key.
     */
    public function __invoke(string $key): Response
    {
        abort_unless(hash_equals(IndexNow::key(), $key), 404);

        return response(IndexNow::key(), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
