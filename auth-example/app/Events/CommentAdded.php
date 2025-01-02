<?php

namespace App\Events;

use App\Models\Comment;
use App\Models\User;
use App\Models\Post;
use App\Models\Notification;  
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class CommentAdded implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $notification; // Holds the notification
    public $user;   
    public $post;

    public function __construct(User $user, Comment $comment)
    {
        $this->user = $user;
        $this->post = $comment->post; // Get the post from the comment

        // Create a notification for the comment
        $this->notification = new Notification();
        $this->notification->user_id = $this->post->user_id; // Notify the post owner
        $this->notification->post_id = $this->post->id; // The post that was commented on
        $this->notification->type = 'comment';
        $this->notification->is_read = false;
        $this->notification->save(); // Save notification

        \Log::info('Notification created for comment', ['id' => $this->notification->id]);
    }

    public function broadcastOn()
    {
        return new Channel('notifications');
    }

    public function broadcastWith()
    {
        \Log::info('Broadcasting CommentAdded event with data:', [
            'notification_id' => $this->notification->id,
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'message' => 'New comment by ' . $this->user->name,
            'post_id' => $this->post->id,
            'time' => now()->toDateTimeString(),
        ]);

        return [
            'notification_id' => $this->notification->id, // Broadcast the correct notification ID
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'message' => 'New comment by ' . $this->user->name,
            'post_id' => $this->post->id,
            'time' => now()->toDateTimeString(),
        ];
    }
}
