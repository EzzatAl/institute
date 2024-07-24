<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return \Illuminate\Support\Facades\Redirect::to('http://127.0.0.1:8000/admin');
});
Route::get('test',function (){
    $e=auth()->user();
    Filament\Notifications\Notification::make()->title("berlitz test")
        ->sendToDatabase($e);
    event(new \Filament\Notifications\Events\DatabaseNotificationsSent($e));
});

