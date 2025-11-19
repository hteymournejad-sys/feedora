<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $profile = auth()->user()->profile;
        $answered_questions = auth()->user()->answers()->count();
        $total_questions = \App\Models\Question::count();

        return view('home', compact('profile', 'answered_questions', 'total_questions'));
    }
}