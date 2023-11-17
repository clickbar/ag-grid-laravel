<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DemoController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Demo');
    }
}
