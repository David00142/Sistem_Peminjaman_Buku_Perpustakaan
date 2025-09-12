<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

protected $fillable = [
    'name',
    'email', 
    'password',
    'kelas',    // Pastikan ada
    'role',     // Pastikan ada
    'otp',
    'otp_expires_at'
];
    protected $hidden = [
        'password', 'remember_token',
    ];

    // Optionally, if you want to cast the 'role' to a specific data type (e.g., string):
    // protected $casts = [
    //     'role' => 'string',
    // ];
}