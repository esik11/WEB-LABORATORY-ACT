<?php

namespace App\Listeners;

use App\Events\LikeAdded;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Notification;
use App\Models\User;

class SendLikeNotification implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(LikeAdded $event)
    {
        // Check for null values before accessing properties
        if (!$event->user || !$event->like || !$event->post) {
            \Log::error('LikeAdded event has null properties', [
                'user' => $event->user,
                'like' => $event->like,
                'post' => $event->post,
            ]);
            return; // Exit if any required property is null
        }

        // Log incoming event data
        \Log::info('Handling LikeAdded event', [
            'user_id' => $event->user->id,
            'like_id' => $event->like->id,
            'post_id' => $event->post->id,
            'message' => $event->user->name . ' liked a post',
        ]);

        // Get post owner's user ID
        $postOwnerId = $event->post->user_id;

        // Check if post owner exists before creating notification
        if (User::find($postOwnerId)) {
            // Create a notification for post owner
            Notification::create([
                'user_id' => $postOwnerId,
                'post_id' => $event->post->id,
                'like_id' => $event->like->id,
                'type' => 'like',
                'is_read' => false,
            ]);

            \Log::info('Notification created for post owner', [
                'notification_id' => optional($notification)->id,
                'post_owner_id' => $postOwnerId,
                'post_id' => $event->post->id,
            ]);
        } else {
            \Log::error('Post owner not found for notification', [
                'post_owner_id' => $postOwnerId,
            ]);
        }
    }
}
