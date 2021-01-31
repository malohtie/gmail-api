<?php

use App\Http\Controllers\AccountController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::view('', 'accounts');

Route::get('accounts', [AccountController::class, 'index'])->name('account.index');
Route::post('accounts/add', [AccountController::class, 'add'])->name('account.add');
Route::patch('accounts/status/{account}', [AccountController::class, 'status'])->name('account.status');
