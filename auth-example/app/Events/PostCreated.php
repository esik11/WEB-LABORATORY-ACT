<?php

namespace App\Events;

use App\Models\Post;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Log;

class PostCreated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $notification;
    public $user;
    public $post;

    public function __construct(User $user, Post $post)
    {
        $this->user = $user;
        $this->post = $post;
        $this->post->save(); // Save post first
    
        // Check existing notifications
        $notification = new Notification();
        $notification->user_id = $user->id;
        $notification->post_id = $post->id;
        $notification->type = 'post';
        $notification->is_read = false;
        $notification->save();
        Log::info('Notification created', ['id' => $notification->id]);
        $this->notification = $notification;
    }

    public function broadcastOn()
    {
        return new Channel('notifications');
    }

    public function broadcastAs()
    {
        return 'post.created';
    }

    public function broadcastWith()
    {
        Log::info('Broadcasting notification', ['id' => $this->notification->id]);
        return [
            'notification_id' => $this->notification->id, // Use actual notification ID
            'user_id' => $this->user->id,
            'name' => $this->user->name,
            'message' => 'New post created by ' . $this->user->name,
            'post_id' => $this->notification->post_id,
            'time' => now()->toDateTimeString(),
        ];
    }
}