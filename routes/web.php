<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\FromController;
use App\Http\Controllers\VacationController;
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

Route::redirect('', 'accounts');

Route::get('apis', [ApiController::class, 'index'])->name('api.index');
Route::patch('apis/status/{api}', [ApiController::class, 'status'])->name('api.status');
Route::post('apis/add', [ApiController::class, 'add'])->name('api.add');
//Route::delete('apis/delete/{api}', [ApiController::class, 'delete'])->name('api.delete');

Route::get('accounts', [AccountController::class, 'index'])->name('account.index');
Route::post('accounts/add', [AccountController::class, 'add'])->name('account.add');
Route::patch('accounts/status/{account}', [AccountController::class, 'status'])->name('account.status');
Route::get('accounts/auth/{account}', [AccountController::class, 'auth'])->name('account.auth');
Route::delete('accounts/delete/{account}', [AccountController::class, 'delete'])->name('account.delete');
Route::get('accounts/callback', [AccountController::class, 'callbackAuth'])->name('account.callback');

Route::get('froms', [FromController::class, 'index'])->name('froms.index');
Route::post('froms/{account}', [FromController::class, 'make'])->name('froms.make');

Route::get('vacations', [VacationController::class, 'index'])->name('vacations.index');
Route::post('vacations/{account}', [VacationController::class, 'make'])->name('vacations.make');
