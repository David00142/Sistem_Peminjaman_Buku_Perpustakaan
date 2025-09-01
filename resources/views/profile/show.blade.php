<!-- resources/views/profile/show.blade.php -->

@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
    <h1>Profil Pengguna</h1>
    <p>Nama: {{ Auth::user()->name }}</p>
    <p>Email: {{ Auth::user()->email }}</p>
    <p>Role: {{ $user->role }}</p>  <!-- Displaying the role -->
    <!-- Tampilkan informasi lainnya yang diperlukan -->

    <!-- profile/show.blade.php -->



@endsection
