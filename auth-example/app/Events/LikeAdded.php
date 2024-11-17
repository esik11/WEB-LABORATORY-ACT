<?php

namespace App\Events;

use App\Models\Like;
use App\Models\User;
use App\Models\Post;
use App\Models\Notification;  // Ensure this is imported
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class LikeAdded implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $like;
    public $user;   
    public $post;

    public function __construct(User $user, Like $like)
    {
        $this->user = $user;
        $this->like = $like;
        $this->post = $like->post;
    }

    public function broadcastOn()
    {
        return new Channel('notifications');
    }

    public function broadcastWith()
    {
        \Log::info('Broadcasting LikeAdded event with data:', [
            'like_id' => $this->like->id,
            'user_id' => $this->user->id,
            'message' => $this->user->name . ' liked a post',
            'post_id' => $this->post->id,
            'time' => now()->toDateTimeString(),
        ]);


        // Broadcasting the data for Pusher
        return [
            'like_id' => $this->like->id,
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'message' => $this->user->name . ' liked a post',
            'post_id' => $this->post->id,
            'time' => now()->toDateTimeString(),
        ];
    }
}

