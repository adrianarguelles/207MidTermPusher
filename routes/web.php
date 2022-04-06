<?php

use Illuminate\Support\Facades\Route;
use App\Events\StatusLinked;
use App\Events\Message;
use App\Http\Controllers\ChatsController;
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

// Route::get('/', function () {
//     return view('index');
// });

Route::get('/login', function() {
    return view('login');
});

Route::post('/send-message',function(Request $request){
    event(
        new Message(
            $request->input('username'),
            $request->input('message')
        )
    );
    return ["success"=> true];
});

Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/chats', [ChatsController::class, 'index']);