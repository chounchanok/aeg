<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PageController;

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

Route::get('dark-mode-switcher', [DarkModeController::class, 'switch'])->name('dark-mode-switcher');
Route::get('color-scheme-switcher/{color_scheme}', [ColorSchemeController::class, 'switch'])->name('color-scheme-switcher');
Route::get('/reset-password/{token}', function (string $token) {
    return redirect('https://buildsmooth.champagne.orangeworkshop.info/reset?token=' . $token);
})->name('password.reset');
Route::get('/reset', function () {
    $token = request()->input('token');
    $email = request()->input('email');
    return view('users.reset', ['token' => $token, 'email' => $email]); })
->name('password.reset');

Route::controller(AuthController::class)->middleware('loggedin')->group(function() {
    Route::get('login', 'loginView')->name('login');
    Route::post('login', 'login')->name('login.check');
});