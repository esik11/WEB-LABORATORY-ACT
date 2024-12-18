<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\LikeController;
Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::middleware(['auth'])->group(function () {
Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
Route::get('/posts', [PostController::class, 'index']);
Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
Route::delete('/posts/{id}', [PostController::class, 'destroy']);


    // For comments
    Route::post('/comments', [CommentController::class, 'store']);
    Route::get('/posts/{post}/comments', [CommentController::class, 'index']);
    Route::put('/comments/{id}', [CommentController::class, 'updateComment']); // Add this line
    Route::delete('/comments/{id}', [CommentController::class, 'destroyComment'])->name('comments.destroy');

// For likes
Route::get('/posts', [LikeController::class, 'index']);
Route::post('/likes', [LikeController::class, 'toggleLike']);  // Use POST for toggle like/unlike

Route::post('/notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead']);

// Route to fetch unread notifications
Route::get('notifications/unread', [NotificationController::class, 'fetchUnreadNotifications']);
// Route to fetch all notifications
Route::get('notifications/all', [NotificationController::class, 'fetchAllNotifications']);
// Route to clear all notifications
Route::post('/clear-notifications', [NotificationController::class, 'clearNotifications'])->name('clear-notifications');


});
