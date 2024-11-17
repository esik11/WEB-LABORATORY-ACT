var app = angular.module('postApp', []);

app.controller('PostController', ['$scope', '$http', '$timeout', function($scope, $http, $timeout) {  // Inject $timeout here
    $scope.currentUserId = window.currentUserId; // Assign the current user ID from the global scope

    $scope.post = { content: '' };
    $scope.successMessage = '';
    $scope.posts = [];
    $scope.newComment = {};
    $scope.editPostContent = {};
    $scope.editingPostId = null; // To track the post being edited
    $scope.editCommentContent = {};
    $scope.editingCommentId = null; // To track the comment being edited
// Load notifications from localStorage on initial load
$scope.notifications = JSON.parse(localStorage.getItem('notifications')) || [];
$scope.unreadCount = $scope.notifications.filter(n => !n.is_read).length;

    const pusher = new Pusher('2ed110f146427c8c5b59', {
        cluster: 'ap1',
        encrypted: true
    });

    // Binding the event from Pusher for real-time comment updates
    var channel = pusher.subscribe('notifications');

    channel.bind('App\\Events\\CommentAdded', function(data) {
        console.log("Comment Notification Data Received:", data);
        if ($scope.loggedInUser && $scope.loggedInUser.id === data.user_id) return;

        // Create notification for comment addition
        const notification = {
            id: data.notification_id, // Add ID using comment ID
            comment_id: data.comment_id,
            user_id: data.user_id,
            username: data.user_name,
            message: data.message,
            post_id: data.post_id,
            time: new Date(data.time).toLocaleTimeString(),
            is_read: false
        };

        // Update notifications
        $scope.notifications.push(notification);
        $scope.unreadCount++;
        localStorage.setItem('notifications', JSON.stringify($scope.notifications));

        // Find the post the comment belongs to and push the new comment to it
        const post = $scope.posts.find(p => p.id === data.post_id);
        if (post) {
            post.comments = post.comments || []; // Ensure it's initialized
            post.comments.push({
                id: data.comment_id,
                user_id: data.user_id,
                user: {
                    id: data.user_id,
                    name: data.user_name
                },
                comment: data.message,
                created_at: data.time
            });
        }

        $scope.$apply(); // Ensure the UI updates
    });
   // Real-time post creation handler
   channel.bind('App\\Events\\PostCreated', function(data) {
    // Log the PostCreated event data for debugging purposes
    console.log("Post Created Notification Data Received:", data);

    const post = {
        id: data.post_id,
        user_id: data.user_id,
        user: { name: data.name },
        content: data.message,
        time: data.time
    };
    
    // Add the new post to the list of posts
    $scope.posts.push(post);

    // Create a notification for the new post
    const notification = {
        id: new Date().getTime(),
        post_id: data.post_id,
        user_id: data.user_id,
        username: data.name,
        message: `New post created by ${data.name}: "${data.message}"`,
        time: new Date(data.time).toLocaleTimeString(),
        is_read: false
    };

    // Add the notification and update unread count
    $scope.notifications.push(notification);
    $scope.unreadCount++;
    localStorage.setItem('notifications', JSON.stringify($scope.notifications));

    // Log the updated notifications array (optional for debugging)
    console.log("Updated Notifications Array:", $scope.notifications);

    // Ensure the view updates
    $scope.$apply();
});


    // Function to create a new post
    $scope.createPost = function() {
        $http.post('/posts', $scope.post).then(function(response) {
            $scope.successMessage = response.data.message;
            $scope.post.content = '';
            $scope.loadPosts();
        }, function(error) {
            console.error("Error creating post:", error);
            alert("There was an error creating the post.");
        });
    };
    channel.bind('App\\Events\\LikeAdded', function(data) {
        console.log("Like Notification Data Received:", data);
    
        if ($scope.loggedInUser && $scope.loggedInUser.id === data.user_id) return;
    
        // Create notification for like
        const notification = {
            id: new Date().getTime(), // Use timestamp as unique ID
            post_id: data.post_id,
            user_id: data.user_id,
            username: data.user_name,
            message: data.message,
            time: new Date(data.time).toLocaleTimeString(),
            is_read: false
        };
    
        // Log notification data before pushing to $scope.notifications
        console.log("Notification Data Being Added:", notification);
    
        // Update notifications
        $scope.notifications.push(notification);
        $scope.unreadCount++;
    
        // Log the updated notifications array
        console.log("Updated Notifications Array:", $scope.notifications);
    
        // Save notifications in localStorage
        localStorage.setItem('notifications', JSON.stringify($scope.notifications));
    
        // Find the post and update the like count and liked status
        const post = $scope.posts.find(p => p.id === data.post_id);
        if (post) {
            post.likes_count = data.likes_count; // Assuming you're sending the updated like count
            post.liked_by_user = data.liked_by_user; // Assuming you want to track who liked the post
        }
    
        // Update the view
        $scope.$apply();
    });
    
    // Function to load posts from the server
    $scope.loadPosts = function() {
        $http.get('/posts').then(function(response) {
            $scope.posts = response.data;
            $scope.posts.forEach(post => {
                post.liked = post.liked_by_user;
                post.comments = [];
                $scope.loadComments(post.id);
                if (!post.likes) {
                    post.likes = [];
                }
            });
        }, function(error) {
            console.error("Error loading posts:", error);
        });
    };

    // Function to load comments for a specific post
    $scope.loadComments = function(postId) {
        $http.get('/posts/' + postId + '/comments').then(function(response) {
            const post = $scope.posts.find(p => p.id === postId);
            if (post) {
                post.comments = response.data;
            }
        }, function(error) {
            console.error("Error loading comments:", error);
        });
    };

    // Function to add a comment to a post
    $scope.addComment = function(postId) {
        const commentText = $scope.newComment[postId];
        if (!commentText || !commentText.trim().length) {
            alert("Comment cannot be empty");
            return;
        }
    
        const commentData = { post_id: postId, comment: commentText.trim() };
    
        $http.post('/comments', commentData).then(function(response) {
            const post = $scope.posts.find(p => p.id === postId);
            if (post) {
                post.comments.push(response.data);
                $scope.newComment[postId] = '';
    
                // Use $timeout to safely trigger a digest
                $timeout(function() {
                    // Ensure AngularJS updates the view properly
                    $scope.$digest();
                }, 0);
            }
        }, function(error) {
            console.error("Error adding comment:", error);
        });
    };

    // Function to open the edit form for a comment
    $scope.openEditCommentForm = function(comment) {
        if (comment.user_id === $scope.currentUserId) { // Only open if the user is the owner
            $scope.editingCommentId = comment.id;
            $scope.editCommentContent[comment.id] = comment.comment;
        } else {
            alert("You do not have permission to edit this comment.");
        }
    };

    // Function to update a comment
    $scope.updateComment = function(commentId) {
        const updatedComment = $scope.editCommentContent[commentId];
        if (!updatedComment || !updatedComment.trim().length) {
            alert("Comment cannot be empty");
            return;
        }

        $http.put('/comments/' + commentId, { comment: updatedComment.trim() })
            .then(function(response) {
                alert(response.data.message);
                $scope.editingCommentId = null;
                $scope.loadPosts();
            }, function(error) {
                console.error("Error updating comment:", error);
                alert("There was an error updating the comment.");
            });
    };

    // Function to delete a comment
    $scope.deleteComment = function(commentId) {
        if (confirm("Are you sure you want to delete this comment?")) {
            $http.delete('/comments/' + commentId)
                .then(function(response) {
                    alert(response.data.message);
                    $scope.loadPosts();
                }, function(error) {
                    console.error("Error deleting comment:", error);
                    alert("There was an error deleting the comment.");
                });
        }
    };

    // Function to toggle like
    $scope.toggleLike = function(post) {
        const userId = $scope.currentUserId;
        $http.post('/likes', { post_id: post.id }).then(function(response) {
            if (post.liked_by_user) {
                post.liked_by_user = false;
                post.likes = post.likes.filter(like => like.user_id !== userId);
            } else {
                post.liked_by_user = true;
                post.likes.push({ user_id: userId });
            }

            post.likes_count = post.likes.length;

            // Update success message
            $scope.successMessage = response.data.message;

            // Clear message after 3 seconds
            setTimeout(function() {
                $scope.successMessage = '';
                $scope.$apply();  // Make sure Angular re-renders
            }, 3000);

        }, function(error) {
            console.error("Error toggling like:", error);
        });
    };
    $scope.loadNotifications = function() {
        $http.get('/notifications').then(function(response) {
            $scope.notifications = response.data;
            $scope.unreadCount = $scope.notifications.filter(n => !n.is_read).length;
            localStorage.setItem('notifications', JSON.stringify($scope.notifications));
        }, function(error) {
            console.error("Error loading notifications:", error);
        });
    };
    
    $scope.markAsRead = function(notification) {
        notification.is_read = true;
        $scope.unreadCount = $scope.notifications.filter(n => !n.is_read).length;
    
        $http.post('/notifications/' + notification.id + '/mark-as-read')
            .then(function(response) {
                console.log('Notification marked as read:', response.data);
                // Update localStorage
                localStorage.setItem('notifications', JSON.stringify($scope.notifications));
            })
            .catch(function(error) {
                console.error('Error marking notification as read:', error);
            });
    };
    
    
    $scope.clearNotifications = function() {
        // Clear notifications from the front-end
        $scope.notifications = [];
        $scope.unreadCount = 0;
        localStorage.removeItem('notifications'); // Clear from localStorage
    
        // Send a request to the backend to clear all notifications
        $http.post('/clear-notifications')
            .then(function(response) {
                console.log("Notifications cleared successfully.");
            }, function(error) {
                console.log("Error clearing notifications:", error);
            });
    };
    
    
    

    // Function to open the edit form for a post
    $scope.openEditForm = function(post) {
        if (post.user_id === $scope.currentUserId) { // Only open if the user is the owner
            $scope.editingPostId = post.id;
            $scope.editPostContent[post.id] = post.content;
        } else {
            alert("You do not have permission to edit this post.");
        }
    };

    // Function to update a post
    $scope.updatePost = function(postId) {
        const updatedContent = $scope.editPostContent[postId];
        if (!updatedContent || !updatedContent.trim().length) {
            alert("Content cannot be empty");
            return;
        }

        $http.put('/posts/' + postId, { content: updatedContent.trim() })
            .then(function(response) {
                alert(response.data.message);
                $scope.editingPostId = null;
                $scope.loadPosts();
            }, function(error) {
                console.error("Error updating post:", error);
                alert("There was an error updating the post.");
            });
    };

    // Function to delete a post
    $scope.deletePost = function(postId) {
        $http.delete('/posts/' + postId)
            .then(function(response) {
                // Success: Remove post from the list or handle the response accordingly
                $scope.posts = $scope.posts.filter(post => post.id !== postId);
                $scope.successMessage = "Post deleted successfully!";
            })
            .catch(function(error) {
                console.error('Error deleting post:', error);
                $scope.errorMessage = "Failed to delete post. Please try again later.";
            });
    };

    // Initial load of posts
    $scope.loadPosts();
}]);
