<?php

use App\Livewire\CartPage;
use App\Livewire\CategoriesPage;
use App\Livewire\HomePage;
use App\Livewire\MyOrderPage;
use App\Livewire\OrderDetailsPage;
use App\Livewire\ProductDetailsPage;
use App\Livewire\ProductsPage;
use App\Livewire\SuccessPage;
use Illuminate\Support\Facades\Route;

Route::get('/', HomePage::class);
Route::get('/categories', CategoriesPage::class);
Route::get('/products', ProductsPage::class);
Route::get('/cart', CartPage::class);
Route::get('/products/{slug}', ProductDetailsPage::class);
Route::get('/my-order', MyOrderPage::class);
Route::get('/my-order/{order}', OrderDetailsPage::class);
Route::get('/success', SuccessPage::class);

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
