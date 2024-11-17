<?php

namespace App\Events;

use App\Models\Comment;
use App\Models\Post; // Add this import
use App\Models\User;
use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CommentAdded implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $comment;
    public $user;
    public $post; // Add this property

    public function __construct(User $user, Comment $comment)
    {
        $this->user = $user;
        $this->comment = $comment;
        $this->post = $comment->post; // Retrieve the associated post
    }

    // The channel on which the event will be broadcast
    public function broadcastOn()
    {
        return new Channel('notifications');
    }

    // Customize the broadcasted data
    public function broadcastWith()
{
    \Log::info('Broadcasting CommentAdded event with data:', [
        'comment_id' => $this->comment->id,
        'user_id' => $this->user->id,
        'message' => $this->user->name . ' commented on a post',
        'post_id' => $this->post->id,
        'time' => now()->toDateTimeString(),
    ]);

    return [
        'comment_id' => $this->comment->id,
        'user_id' => $this->user->id,
        'user_name' => $this->user->name,
        'message' => $this->user->name . ' commented on a post',
        'post_id' => $this->post->id,
        'time' => now()->toDateTimeString(),
    ];
}
}