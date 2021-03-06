<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        return view('home')
            ->with('user', $user ? $user->toArray() : null)
            ->with('games', $user->games()->with('winner', 'looser')->get());
    }
}
