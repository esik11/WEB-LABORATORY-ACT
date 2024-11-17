<?php

namespace App\Listeners;

use App\Events\LikeAdded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
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
        // Log the incoming event data for debugging
        \Log::info('Handling LikeAdded event', [
            'user_id' => $event->user->id,
            'like_id' => $event->like->id,
            'post_id' => $event->like->post_id,
            'message' => $event->user->name . ' liked a post',
        ]);

        // Get the post owner's user ID
        $postOwnerId = $event->like->post->user_id; // Ensure the post relationship is set correctly

        // Log post owner info for debugging
        \Log::info('Creating Notification for post owner', [
            'post_owner_id' => $postOwnerId,
            'post_id' => $event->like->post_id,
            'like_id' => $event->like->id,
        ]);

        // Check if the post owner exists before creating the notification
        if (User::find($postOwnerId)) {
            // Create a notification for the post owner
            Notification::create([
                'user_id' => $postOwnerId,  // Notify the owner of the post
                'post_id' => $event->like->post_id,
                'like_id' => $event->like->id, // Store the like ID for reference
                'type' => 'like',
                'is_read' => false,
            ]);
            \Log::info('Notification created for post owner', [
                'post_owner_id' => $postOwnerId,
                'post_id' => $event->like->post_id,
            ]);
        } else {
            \Log::error('Post owner not found for notification', [
                'post_owner_id' => $postOwnerId,
            ]);
        }
    }
}
