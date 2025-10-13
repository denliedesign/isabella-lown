<?php

use App\Models\Bio;
use App\Models\Headshot;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// routes/web.php
Route::get('/phpinfo', function () {
    return [
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size'       => ini_get('post_max_size'),
        'max_execution_time'  => ini_get('max_execution_time'),
        'max_input_time'      => ini_get('max_input_time'),
    ];
});


Route::view('/dancing', 'dancing')->name('dancing');
Route::view('/teaching', 'teaching')->name('teaching');
Route::view('/creative-direction', 'creative-direction')->name('creative-direction');
Route::view('/stage-choreo', 'stage-choreo')->name('stage-choreo');


Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Volt::route('/admin/portfolio', 'admin.portfolio')
    ->middleware(['auth'])
    ->name('admin.portfolio');

Route::get('/bio', function () {
    $bio = Bio::first();
    $headshots = Headshot::all();

    return view('bio', compact('bio', 'headshots'));
})->name('bio');

Volt::route('/admin/bio', 'admin.bio')
    ->middleware(['auth'])
    ->name('admin.bio');

Volt::route('/admin/headshots', 'admin.headshots')
    ->middleware(['auth'])
    ->name('admin.headshots');


Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');
});

require __DIR__.'/auth.php';
