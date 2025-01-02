<?php

namespace App\Events;

use App\Models\Like;
use App\Models\User;
use App\Models\Post;
use App\Models\Notification;  
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class LikeAdded implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $notification; // Changed to hold notification
    public $user;   
    public $post;

    public function __construct(User $user, Like $like)
    {
        $this->user = $user;
        $this->post = $like->post; // Get the post from the like

        // Create a notification for the like
        $this->notification = new Notification();
        $this->notification->user_id = $this->post->user_id; // Notify the post owner
        $this->notification->post_id = $this->post->id; // The post that was liked
        $this->notification->type = 'like';
        $this->notification->is_read = false;
        $this->notification->save(); // Save notification

        \Log::info('Notification created for like', ['id' => $this->notification->id]);
    }

    public function broadcastOn()
    {
        return new Channel('notifications');
    }

    public function broadcastWith()
    {
        \Log::info('Broadcasting LikeAdded event with data:', [
            'notification_id' => $this->notification->id, // Use actual notification ID
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'message' => $this->user->name . ' liked a post',
            'post_id' => $this->post->id,
            'time' => now()->toDateTimeString(),
        ]);

        return [
            'notification_id' => $this->notification->id, // Broadcast the correct notification ID
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'message' => $this->user->name . ' liked a post',
            'post_id' => $this->post->id,
            'time' => now()->toDateTimeString(),
        ];
    }
}
