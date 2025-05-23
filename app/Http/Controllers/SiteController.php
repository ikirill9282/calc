<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index(Request $request)
    {
      return view('pages.index');
    }

    public function history(Request $request)
    {
      return view('pages.history');
    }

    public function agenst(Request $request)
    {
      return view('pages.agents');
    }
}
