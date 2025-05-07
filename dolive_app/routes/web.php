<?php

use App\Http\Controllers\ProfileController;
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
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';


use App\Http\Controllers\AdminController;

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'indexUsers'])->name('users.index');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    // Add more admin routes here as needed
});



use App\Http\Controllers\VideoController;

// Video Routes (Protected by auth middleware)
Route::middleware(["auth"])->group(function () {
    Route::get("/videos", [VideoController::class, "index"])->name("videos.index");
    Route::get("/videos/create", [VideoController::class, "create"])->name("videos.create");
    Route::post("/videos", [VideoController::class, "store"])->name("videos.store");
    Route::get("/videos/{video}/edit", [VideoController::class, "edit"])->name("videos.edit");
    Route::put("/videos/{video}", [VideoController::class, "update"])->name("videos.update");
    Route::delete("/videos/{video}", [VideoController::class, "destroy"])->name("videos.destroy");
});



use App\Http\Controllers\LivestreamController;

// Livestream Routes (Protected by auth middleware)
Route::middleware(["auth"])->prefix("livestreams")->name("livestreams.")->group(function () {
    Route::get("/", [LivestreamController::class, "index"])->name("index");
    Route::get("/create/{video}", [LivestreamController::class, "create"])->name("create"); // Pass video to prefill
    Route::post("/", [LivestreamController::class, "store"])->name("store");
    Route::get("/{livestream}", [LivestreamController::class, "show"])->name("show");
    Route::post("/{livestream}/start", [LivestreamController::class, "startStream"])->name("start");
    Route::post("/{livestream}/stop", [LivestreamController::class, "stopStream"])->name("stop");
    Route::delete("/{livestream}", [LivestreamController::class, "destroy"])->name("destroy");
    // Route to add/remove destinations could be here or handled within create/edit of livestream job
});

