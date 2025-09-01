<?php
// app/Http/Controllers/ProfileController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
    {
        // Get the logged-in user
        $user = Auth::user();
        
        // Pass user to the view (optional)
        return view('profile.show', compact('user'));
    }
}

