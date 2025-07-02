<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', function () {
    return redirect()->route('products.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/products', 'ProductController@index')->name('products.index');

    Route::middleware('check.access:developer')->group(function () {
        Route::get('/sync-product', [ProductController::class, 'sync'])->name('products.sync');
        Route::post('/product/{encodedId}/upload', 'AssetController@upload')->name('asset.upload');
        Route::post('/product/{encodedId}/delete', 'AssetController@destroy')->name('asset.delete');
    });

    Route::middleware('check.access:developer,design')->group(function () {
        Route::post('/product/{encodedId}/upload-image', 'AssetController@uploadImageOnly')->name('asset.uploadImageOnly');
    });
});

Route::get('/preview/{encodedId}/{filename}', function ($encodedId, $filename) {
    $decodedId = base64_decode(strtr($encodedId, '-_', '+/'));
    $path = public_path("assets/$decodedId/$filename");

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
})->name('file.preview');