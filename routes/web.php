<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Candidate\UploadCv; // Import dulu
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\CandidateDetail;
use App\Livewire\Interview\ChatBot;
use App\Livewire\Interview\VideoRecorder;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('/apply', UploadCv::class)->name('apply');   

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
});

Route::get('/candidates/{candidate}', CandidateDetail::class)->name('candidates.show');

Route::get('/interview/{candidate}', VideoRecorder::class)->name('interview.start');

Route::view('/interview-done', 'interview-done')->name('interview.done');

require __DIR__.'/auth.php';
