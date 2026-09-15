<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * The account page. Everything on it is read through the signed-in user, so one
     * user can never see another's favourites or ratings.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('dashboard', [
            'user' => $user,
            'favouriteRecipes' => $user->favouriteRecipes()->withCardDetails()->get(),
            'ratings' => $user->ratings()->with('recipe')->latest('updated_at')->get(),
        ]);
    }
}
