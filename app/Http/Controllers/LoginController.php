<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function index()
    {
        $title = "Login Page";
        return view('login', ['title' => $title]);
    }
}

