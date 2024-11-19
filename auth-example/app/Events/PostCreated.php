<?php

// app/Events/PostCreated.php

namespace App\Events;

use App\Models\Post;
use App\Models\User;
use App\Models\Notification; // Import the Notification model
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PostCreated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $post;
    public $user;

    public function __construct(User $user, Post $post)
    {
        $this->user = $user;
        $this->post = $post;

        // Create a new notification for the post creation
        $notification = new Notification();
        $notification->user_id = $user->id;
        $notification->post_id = $post->id; // Link to the post
        $notification->type = 'post'; // Set type
        $notification->is_read = false; // Set as unread
        $notification->save(); // Save to get a unique ID

        // Optionally log the creation
        \Log::info('Created notification for post', ['notification_id' => $notification->id]);
    }

    // The channel on which the event will be broadcast
    public function broadcastOn()
    {
        return new Channel('notifications');
    }

    // Customize the broadcasted data
    public function broadcastWith()
    {
        return [
            'notification_id' => $this->post->id, // Use the unique notification ID
            'user_id' => $this->user->id,
            'name' => $this->user->name,
            'message' => 'New post created by ' . $this->user->name,
            'post_id' => $this->post->id,
            'time' => now()->toDateTimeString(),
        ];
    }
}