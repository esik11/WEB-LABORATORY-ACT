<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Profile;
use App\Models\Notification;
use App\Models\User;
use App\Events\PostCreated;
class PostController extends Controller
{
    /**
     * Show the form for creating a new post.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('home'); // Return the view where users can create a post
    }

    /**
     * Store a newly created post in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
{
    // Validate incoming request data
    $request->validate([
        'content' => 'required|string',
        'image' => 'nullable|image|max:2048', // Optional image upload validation
    ]);

    // Create a new post instance
    $post = new Post();
    $post->content = $request->content;
    $post->user_id = Auth::id(); // Associate post with authenticated user

    // Handle image upload if provided
    if ($request->hasFile('image')) {
        $path = $request->file('image')->store('posts', 'public');
        $post->image = $path; // Save image path in database (if you add an image field to your posts table)
    }

    // Save the post to the database
    $post->save();

    // Dispatch the PostCreated event after the post is saved
    event(new PostCreated(Auth::user(), $post)); // Ensure the event is fired with correct data

    // Log the event dispatch for debugging purposes
    Log::info('PostCreated event dispatched', [
        'user_id' => Auth::id(),
        'post_id' => $post->id,
        'message' => 'New post created by ' . Auth::user()->name,
    ]);

    // Return a JSON response indicating success
    return response()->json(['message' => 'Post created successfully!'], 201);
}


    /**
     * Display a listing of posts.
     *
     * @return \Illuminate\View\View
     */
    public function index()
{
    $userId = Auth::id();
    
    // Eager load the 'user' and 'likes' relationship
    $posts = Post::with('user', 'likes')->get();

    foreach ($posts as $post) {
        // Add additional properties if needed
        $post->liked_by_user = $post->likes->contains('user_id', $userId);
        $post->likes_count = $post->likes->count();  // Add the like count
    }

    return response()->json($posts);
}

    
    /**
     * Remove the specified post from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
     // Update an existing post (only by the owner)
     public function update(Request $request, $postId)
     {
         $request->validate([
             'content' => 'required|string|max:255',
         ]);
     
         $post = Post::findOrFail($postId);
     
         if ($post->user_id !== auth()->id()) {
             return response()->json(['message' => 'Unauthorized'], 403);
         }
     
         $post->content = $request->input('content');
         $post->save();
     
         return response()->json(['message' => 'Post updated successfully', 'post' => $post], 200);
     }
     

     public function destroy($id)
{
    try {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found!'], 404);
        }

        if ($post->user_id != Auth::id()) {
            return response()->json(['message' => 'You can only delete your own posts.'], 403);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully!']);
    } catch (\Exception $e) {
        Log::error('Error deleting post: ' . $e->getMessage(), [
            'post_id' => $id,
            'user_id' => Auth::id(),
        ]);
        return response()->json(['message' => 'An error occurred while deleting the post. Please try again later.'], 500);
    }
}

     
}
