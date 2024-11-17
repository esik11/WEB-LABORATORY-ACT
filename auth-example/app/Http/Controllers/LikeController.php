<?php
namespace App\Http\Controllers;

use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use App\Events\LikeAdded;
use App\Listener\SendLikeNotification;
use App\Models\Notification;
class LikeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Function to fetch posts with likes and user's like status
    public function index()
    {
        $userId = Auth::id();

        // Load posts with their likes and the user who created each post
        $posts = Post::with('likes', 'user')->get(); 

        foreach ($posts as $post) {
            // Check if the current user has liked the post
            $post->liked_by_user = $post->likes->contains('user_id', $userId);
            $post->likes_count = $post->likes->count();  // Add the like count

            // Add user name to each post's data
            $post->user_name = $post->user->name; 
        }

        return response()->json($posts);
    }

    // Toggle like or unlike for a post
    // Toggle like or unlike for a post
public function toggleLike(Request $request)
{
    $userId = Auth::id();
    $postId = $request->post_id;

    // Find the post
    $post = Post::findOrFail($postId);

    // Check if the user has already liked the post
    $like = Like::where('user_id', $userId)
                ->where('post_id', $postId)
                ->first();

    if ($like) {
        // If the user has liked the post, delete the like
        $like->delete();
        return response()->json([
            'message' => 'You unliked this post.',
            'liked_by_user' => false,
            'likes_count' => $post->likes_count
        ], 200);
    } else {
        // If the user has not liked the post, create a new like
        $like = Like::create([
            'user_id' => $userId,
            'post_id' => $postId,
        ]);
         
        // Broadcast the event
        broadcast(new LikeAdded(Auth::user(), $like));

        return response()->json([
            'message' => 'You liked this post.',
            'liked_by_user' => true,
            'likes_count' => $post->likes_count
        ], 200);
    }
}

}



// public function likePost($postId)
//     {
//         try {
//             $post = Post::findOrFail($postId);
//             $user = Auth::user();
    
//             // Toggle like
//             $like = $post->likes()->where('user_id', $user->id)->first();
//             if ($like) {
//                 // Unlike the post
//                 $like->delete();
//             } else {
//                 // Like the post
//                 $like = Like::create([
//                     'post_id' => $post->id,
//                     'user_id' => $user->id,
//                 ]);
//                 broadcast(new LikeAdded($user, $like))->toOthers();
//             }
    
//             return response()->json(['like_count' => $post->likes()->count()]);
//         } catch (\Exception $e) {
//             \Log::error('Like Post Error: ' . $e->getMessage(), [
//                 'postId' => $postId,
//                 'userId' => Auth::id(),
//                 'stack' => $e->getTraceAsString(),
//             ]);
//             return response()->json(['error' => 'An error occurred'], 500);
//         }
//     }
//     public function unlikePost($postId)
// {
//     $post = Post::findOrFail($postId);
//     $user = auth()->user(); 

//     // Check if the post is liked by the user
//     $like = $post->likes()->where('user_id', $user->id)->first();

//     if ($like) {
//         $like->delete();
//         return response()->json(['message' => 'Post unliked successfully'], 200);
//     }

//     return response()->json(['message' => 'You have not liked this post'], 400);
// }