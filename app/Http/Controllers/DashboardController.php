<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $favouriteRecipes = $request->user()
            ->favouriteRecipes()
            ->get();

        return view('dashboard', [
            'favouriteRecipes' => $favouriteRecipes,
        ]);
    }
}
