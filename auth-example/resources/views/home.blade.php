@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6" ng-app="postApp" ng-controller="PostController">
    <!-- Notification Dropdown -->
    <div class="dropdown mb-4">
    <button class="btn btn-primary dropdown-toggle" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        Notifications <span id="notification-count">@{{ unreadCount }}</span>
    </button>
    <ul class="dropdown-menu" aria-labelledby="notificationDropdown">
        <li id="notification-list">
            <div ng-repeat="notification in notifications track by $index">
                <a class="dropdown-item" href="#" ng-click="markAsRead(notification)">
                    @{{ notification.username }} - @{{ notification.message }} (@{{ notification.time }})
                </a>
            </div>
            <div ng-if="notifications.length === 0" class="dropdown-item text-center">
                No new notifications
            </div>
        </li>
        <!-- Clear All button -->
        <li>
            <button class="dropdown-item text-center" ng-click="clearNotifications()">Clear All</button>
        </li>
    </ul>
</div>



    <h1 class="text-4xl font-bold mb-6 text-center text-blue-600">Create a New Post</h1>

    <div ng-if="successMessage" class="alert alert-success mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Success!</strong>
        <span class="block sm:inline" ng-bind="successMessage"></span>
    </div>

    <form ng-submit="createPost()" class="bg-white shadow-lg rounded-lg px-8 pt-6 pb-8 mb-6">
        @csrf
        <div class="mb-4">
            <label for="content" class="block text-sm font-medium text-gray-700">Content</label>
            <textarea ng-model="post.content" required class="mt-1 block w-full h-32 border border-gray-300 rounded-md shadow-sm p-2 focus:ring focus:ring-blue-500 transition duration-150 ease-in-out" placeholder="Write your post content here..."></textarea>
        </div>

        <div class="flex items-center justify-between mb-4">
            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-md text-sm font-medium text-black bg-blue-600 hover:bg-blue-700 focus:outline-none transition duration-300 ease-in-out">
                <i class="fas fa-paper-plane mr-2"></i> Create Post
            </button>
        </div>
    </form>

    <h2 class="text-3xl font-bold mb-4 text-center">Recent Posts</h2>
    <div class="bg-white shadow-lg rounded-lg p-4">
        <div ng-repeat="post in posts" class="border-b border-gray-200 py-4">
            <h3 class="text-lg font-semibold text-blue-600">@{{ post.user.name }}</h3>
            
            <!-- Display post content or edit form based on editingPostId -->
            <div ng-if="editingPostId === post.id">
                <textarea ng-model="editPostContent[post.id]" class="block w-full border border-gray-300 rounded-md p-2"></textarea>
                <button ng-click="updatePost(post.id)" class="mt-2 inline-flex justify-center py-1 px-3 border border-transparent rounded-md shadow-sm text-sm font-medium bg-blue-600 text-white hover:bg-blue-700">
                    Update
                </button>
                
                <button ng-click="editingPostId = null" class="mt-2 inline-flex justify-center py-1 px-3 border border-transparent rounded-md shadow-sm text-sm font-medium bg-gray-300 text-black hover:bg-gray-400"> Cancel </button>
            </div>
            <p class="text-gray-800" ng-if="editingPostId !== post.id">@{{ post.content }}</p>

            <!-- Edit button visible only to post owner -->
            <button ng-click="openEditForm(post)" ng-if="post.user_id === currentUserId" class="text-sm text-blue-600 hover:text-blue-800 mt-2">
                Edit Post
            </button>
            <button ng-click="deletePost(post.id)" ng-if="post.user_id === currentUserId" class="text-sm text-red-600 hover:text-red-800 mt-2">
    Delete Post
</button>

            <!-- Like/Unlike functionality -->
            <button ng-click="toggleLike(post)" class="text-sm mt-2">
                <span ng-if="post.liked" class="text-red-600"><i class="fas fa-heart"></i> Unlike</span>
                <span ng-if="!post.liked" class="text-gray-500"><i class="far fa-heart"></i> Like</span>
            </button>
            <span class="text-sm text-gray-600 ml-2">@{{ post.likes_count }} likes</span>

            <h4 class="text-sm font-semibold mt-4">Comments:</h4>
            <div ng-repeat="comment in post.comments" class="mb-2">
                <!-- Display comment content or edit form based on editingCommentId -->
                <div ng-if="editingCommentId === comment.id">
                    <textarea ng-model="editCommentContent[comment.id]" class="block w-full border border-gray-300 rounded-md p-2"></textarea>
                    <button ng-click="updateComment(comment.id)" class="mt-2 inline-flex justify-center py-1 px-3 border border-transparent rounded-md shadow-sm text-sm font-medium bg-blue-600 text-white hover:bg-blue-700">
                        Update
                    </button>
                    <button ng-click="editingCommentId = null" class="mt-2 inline-flex justify-center py-1 px-3 border border-transparent rounded-md shadow-sm text-sm font-medium bg-gray-300 text-black hover:bg-gray-400">
                        Cancel
                    </button>

                </div>
                <p class="text-gray-700" ng-if="editingCommentId !== comment.id">@{{ comment.comment }}</p>

                <!-- Edit and delete buttons visible only to comment owner -->
                <button ng-click="openEditCommentForm(comment)" ng-if="comment.user_id === currentUserId" class="text-xs text-blue-500 hover:text-blue-700">
                    Edit
                </button>
                <button ng-click="deleteComment(comment.id)" ng-if="comment.user_id === currentUserId" class="text-xs text-red-500 hover:text-red-700 ml-2">
                    Delete
                </button>
            </div>

            <!-- Comment form -->
            <textarea ng-model="newComment[post.id]" class="block w-full border border-gray-300 rounded-md mt-2 p-2" placeholder="Add a comment..."></textarea>
            <button ng-click="addComment(post.id)" class="mt-2 inline-flex justify-center py-1 px-3 border border-transparent rounded-md shadow-sm text-sm font-medium bg-green-600 text-white hover:bg-green-700">
                Post Comment
            </button>
        </div>
    </div>
</div>
@endsection
