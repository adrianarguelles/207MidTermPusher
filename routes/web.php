<?php

use Illuminate\Support\Facades\Route;
use App\Events\StatusLinked;
use App\Events\Message;
use App\Http\Controllers\ChatsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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

Auth::routes();

Route::get('/', [App\Http\Controllers\ChatsController::class, 'index'])->name('home');

Route::get('/messages', [ChatsController::class, 'fetchMessages']);
Route::post('/messages', [ChatsController::class, 'sendMessage']);

//adrian chatroom addition
Route::get('/rooms', [App\Http\Controllers\ChatsController::class, 'fetchChatrooms']);
Route::post('/newRoom', [ChatsController::class, 'addRoom']);
Route::post('/addMember', [ChatsController::class, 'addMember']);
Route::get('/test', [ChatsController::class, 'test']);

Route::get('/edit-profile', [ProfileController::class, 'showTestPage']);
Route::get('/profile', [ProfileController::class, 'getProfile']);
Route::post('/profile', [ProfileController::class, 'updateProfile']);
Route::get('/users', [ProfileController::class, 'searchProfile']);
