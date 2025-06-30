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

// Setelah login langsung ke list produk
Route::get('/home', function () {
    return redirect()->route('products.index');
});

// Middleware utama: user harus login
Route::middleware('auth')->group(function () {

    // 📦 List produk (semua role bisa akses)
    Route::get('/products', 'ProductController@index')->name('products.index');
    // 🔄 Sinkronisasi data produk (hanya developer)
    Route::middleware('check.access:developer')->group(function () {
        Route::get('/sync-product', [ProductController::class, 'sync'])->name('products.sync');
        // Upload image + video
        Route::post('/product/{id}/upload', 'AssetController@upload')->name('asset.upload');
        // Hapus file asset
        Route::post('/product/{id}/delete', 'AssetController@destroy')->name('asset.delete');
    });

    // 📷 Upload hanya image (developer & design)
    Route::middleware('check.access:developer,design')->group(function () {
        Route::post('/product/{id}/upload-image', 'AssetController@uploadImageOnly')->name('asset.uploadImageOnly');
    });
});