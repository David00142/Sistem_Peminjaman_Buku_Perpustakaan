<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'kelas', 'role' // Tambahkan 'kelas' di sini
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    // Optionally, if you want to cast the 'role' to a specific data type (e.g., string):
    // protected $casts = [
    //     'role' => 'string',
    // ];
}