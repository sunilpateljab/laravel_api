<?php

use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::get('/products', function(){
// 	return Product::all();
// });

// public route
Route::post('/register', [ProductController::class, 'register']);
Route::post('/addproduct', [ProductController::class, 'store']);
Route::get('/singleproduct/{id}', [ProductController::class, 'show']);
Route::put('/updateproduct/{id}', [ProductController::class, 'update']);
Route::delete('/deleteproduct/{id}', [ProductController::class, 'destroy']);
Route::get('/searchproduct/{name}', [ProductController::class, 'search']);
Route::post('/download/{id}', [ProductController::class, 'download']);
Route::post('/email', [ProductController::class, 'email']);


// protected route
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/productcategory', [ProductController::class, 'getproductbycategory']);
    Route::post('/login', [ProductController::class, 'login']);
    Route::post('/logout', [ProductController::class, 'logout']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
