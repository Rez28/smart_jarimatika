<?php

use Illuminate\Support\Facades\Route;
// 1. Ubah rute utama (/) untuk langsung ke halaman praktik
Route::get('/', function () {
    // Arahkan langsung ke view practice
    return view('welcome'); 
});