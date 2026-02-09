<?php

use Illuminate\Support\Facades\Route;

Route::get('/', App\Livewire\SundaChat::class);
Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile');
