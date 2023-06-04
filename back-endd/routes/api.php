<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::prefix('voting')->group(function () {
    
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',[AuthController::class,'login']);
    Route::get('pemilihan', [AdminController::class, 'pemilihan']); //buat campaign dan mengambil data pemilihan
    Route::get('user/{id}',[AdminController::class, 'showuserbyid']); //data user/id    doen
    
});




//belum protect midwalre
Route::middleware(['admin.api'])->prefix('admin')->group(function () {
    // Route-route untuk admin di sini

    //error
    
    //done
    Route::get('dashboard/{IdPemilihan}', [AdminController::class, 'dashboard']);   //dashboard per pemilihan data semua vote di function ini
    Route::post('vote/{IdPemilihan}', [AdminController::class, 'vote']);   //mendapatkan data voting dengan menampilkan user, dan sisa yang belum vote *kandidat bisa juga
    
    Route::get('kandidat', [AdminController::class, 'showkandidat']); //tampil semua data pemilihan
    Route::get('kandidat/{id}', [AdminController::class, 'showkandidatbyid']);    //tampil data kandidat sesuai id
    Route::get('kandidat/acc/{IdKandidat}', [AdminController::class, 'acckandidat']);  //buatkan agar kandidat di setujui = true 
    Route::get('kandidat/noacc/{IdKandidat}', [AdminController::class, 'noacckandidat']);  //buatkan agar kandidat di setujui = true
    Route::post('pemilihan', [AdminController::class, 'pemilihan']);    //input pemilihan
    Route::get('pemilihan', [AdminController::class, 'showpemilihan']); //tampil semua data pemilihan
    Route::get('pemilihan/{id}', [AdminController::class, 'showpemilihanbyid']);    //tampil data pemilihan sesuai id
    Route::get('pemilihan/acc/{Idpemilihan}', [AdminController::class, 'accpemilihan']);  //buatkan agar pemilihan di setujui = true 
    Route::get('pemilihan/noacc/{Idpemilihan}', [AdminController::class, 'noaccpemilihan']);  //buatkan agar pemilihan di setujui = true
    Route::get('profile',[AdminController::class, 'profile']);  //tampil data user yang sudah login
    Route::put('profile/edit/{IdUser}',[AdminController::class, 'editprofile']); //error di bagian mengambil data iduser di profile()
    Route::post('periode',[AdminController::class, 'periode']);  //input periode    done
    Route::get('sp',[AdminController::class, 'showsp']);  //data periode  done
    Route::get('periode/{id}',[AdminController::class, 'showperiodebyid']); //data periode/id    doen
    Route::post('user',[AdminController::class, 'user']);  //input user    done
    Route::get('user',[AdminController::class, 'showuser']);  //data user  done
    
    
});


//belum protect midwalre
Route::middleware(['user.api'])->prefix('user')->group(function () {
    // Route-route untuk admin di sini
    
    Route::get('dashboard/{IdPemilihan}', [UserController::class, 'dashboard']);   //dashboard per pemilihan data semua vote di function ini
    Route::post('vote/{IdPemilihan}', [UserController::class, 'vote']);   //mendapatkan data voting dengan menampilkan user, dan sisa yang belum vote *kandidat bisa juga
    Route::get('pemilihan', [UserController::class, 'showpemilihan']); //tampil semua data pemilihan
    
    Route::get('profile',[UserController::class, 'profile']);  //tampil data user yang sudah login
    Route::post('profile/edit/{IdUser}',[UserController::class, 'editprofile']); //error di bagian mengambil data iduser di profile()
    Route::post('kandidat/{idpemilihan}', [UserController::class, 'kandidat']);  //done pengajuan kandidat untuk user    
});



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
