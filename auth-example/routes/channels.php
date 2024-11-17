<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('notifications.{userId}', function ($user, $userId) {
    return $user != null; // Authorize all authenticated users
});

Broadcast::channel('likes', function ($user) {
    return auth()->check(); // or specify authorization logic
});
Broadcast::channel('posts.{postId}', function ($user, $postId) {
    // Optionally, authorize the user based on a condition like if they are the owner of the post
    // For now, authorize all authenticated users
    return auth()->check(); // Or you can add additional authorization logic
});


