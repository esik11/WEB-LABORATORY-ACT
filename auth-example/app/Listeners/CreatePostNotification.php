<?php

namespace App\Listeners;

use App\Events\PostCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Notification;


class CreatePostNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct()
    {
        //
    }

    public function handle(PostCreated $event)
    {
        // Log the incoming event data for debugging
        \Log::info('Handling PostCreated event', [
            'user_id' => $event->user->id,
            'post_id' => $event->post->id,
            'message' => 'New post created by ' . $event->user->name,
        ]);

        // Create a notification for the post owner
        Notification::create([
            'user_id' => $event->post->user_id,
            'post_id' => $event->post->id,
            'type' => 'post',
            'is_read' => false,
        ]);

        // Log notification creation
        \Log::info('Notification created for post owner', [
            'post_owner_id' => $event->post->user_id,
            'post_id' => $event->post->id,
        ]);
    }
}
