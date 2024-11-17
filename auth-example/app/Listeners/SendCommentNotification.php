<?php

namespace App\Listeners;

use App\Events\CommentAdded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Notification; // Ensure this is imported
class SendCommentNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\CommentAdded  $event
     * @return void
     */
    public function handle(CommentAdded $event)
{
    \Log::info('Notification about comment added to post:', [
        'user_id' => $event->post->user_id,
        'post_id' => $event->post->id,
        'type' => 'comment',
    ]);

    Notification::create([
        'user_id' => $event->post->user_id,  // Notify the owner of the post
        'post_id' => $event->post->id,
        'type' => 'comment',
        'is_read' => false,
    ]);
}

}

