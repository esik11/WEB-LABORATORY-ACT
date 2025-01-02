<?php

namespace App\Listeners;

use App\Events\CommentAdded;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Notification;
use App\Models\User;

class SendCommentNotification implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(CommentAdded $event)
    {
        // Check for null values before accessing properties
        if (!$event->user || !$event->post) {
            \Log::error('CommentAdded event has null properties', [
                'user' => $event->user,
                'post' => $event->post,
            ]);
            return; // Exit if any required property is null
        }

        // Log incoming event data
        \Log::info('Handling CommentAdded event', [
            'user_id' => $event->user->id,
            'post_id' => $event->post->id,
            'message' => 'New comment by ' . $event->user->name,
        ]);

        // Get post owner's user ID
        $postOwnerId = $event->post->user_id;

        // Check if post owner exists before creating notification
        if (User::find($postOwnerId)) {
            // Create a notification for post owner
            Notification::create([
                'user_id' => $postOwnerId,  // Notify the owner of the post
                'post_id' => $event->post->id, // The post that was commented on
                'comment_id' => optional($event)->comment ? $event->comment->id : null, // Store the comment ID for reference if needed
                'type' => 'comment',
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
