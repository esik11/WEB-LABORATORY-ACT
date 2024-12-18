<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    protected $listen = [
        \App\Events\PostCreated::class => [
        ],
        \App\Events\CommentAdded::class => [
            \App\Listeners\SendCommentNotification::class,
        ],
        \App\Events\LikeAdded::class => [
            \App\Listeners\SendLikeNotification::class,
        ],
        

    ];
    public function boot(): void
    {
        //
    }
}
