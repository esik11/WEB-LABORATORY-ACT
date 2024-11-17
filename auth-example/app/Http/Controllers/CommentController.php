<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use App\Events\CommentAdded;
class CommentController extends Controller
{
    public function store(Request $request)
    {
        \Log::info('Comment Request:', $request->all()); // Log the request
    
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'comment' => 'required|string|max:500',
        ]);
    
        $comment = Comment::create([
            'post_id' => $request->post_id,
            'user_id' => Auth::id(),
            'comment' => $request->comment,
        ]);
    
        $comment->load('user'); // Load the user with the comment
    
        // Fire the CommentAdded event to notify the post owner
        event(new CommentAdded(Auth::user(), $comment));
    
        return response()->json($comment, 201);
    }
    
    

    public function index($postId)
    {
        $comments = Comment::with('user')->where('post_id', $postId)->get();
        return response()->json($comments);
    }
    public function updateComment(Request $request, $id)
{
    $comment = Comment::find($id);

    if (!$comment) {
        return response()->json(['message' => 'Comment not found!'], 404);
    }

    // Check if the logged-in user is the owner of the comment
    if ($comment->user_id != Auth::id()) {
        return response()->json(['message' => 'You can only edit your own comments.'], 403);
    }

    $comment->comment = $request->input('comment');
    $comment->save();

    return response()->json(['message' => 'Comment updated successfully!']);
}

public function destroyComment($id)
{
    $comment = Comment::find($id);

    if (!$comment) {
        return response()->json(['message' => 'Comment not found!'], 404);
    }

    // Check if the logged-in user is the owner of the comment
    if ($comment->user_id != Auth::id()) {
        return response()->json(['message' => 'You can only delete your own comments.'], 403);
    }

    // Delete the comment
    $comment->delete();

    return response()->json(['message' => 'Comment deleted successfully!']);
}

}



// public function addComment(Request $request, $postId)
// {
//     $request->validate([
//         'comment' => 'required|string|max:255',
//     ]);

//     $post = Post::findOrFail($postId);
//     $user = Auth::user();

//     $comment = Comment::create([
//         'post_id' => $post->id,
//         'user_id' => $user->id,
//         'comment' => $request->comment,
//     ]);

//     // Dispatch the CommentAdded event
//     broadcast(new CommentAdded($user, $comment))->toOthers();

//     return response()->json($comment);
// }
//     // Edit an existing post (only by the owner)
//     public function edit(Request $request, Post $post)
// {
//     // Ensure only the post owner can edit the post
//     if ($post->user_id !== auth()->id()) {
//         return response()->json(['message' => 'Unauthorized'], 403);
//     }

//     // Perform validation
//     $request->validate([
//         'content' => 'required|string|max:255',
//     ]);

//     $post->content = $request->input('content');
//     $post->save();

//     return response()->json($post);
// }
// // Edit an existing comment (only by the owner)
// public function editComment(Request $request, Post $post, Comment $comment)
// {
//     // Ensure only the comment owner can edit the comment
//     if ($comment->user_id !== auth()->id()) {
//         return response()->json(['message' => 'Unauthorized'], 403);
//     }

//     // Perform validation
//     $request->validate([
//         'comment' => 'required|string|max:255', // Validate the 'comment' field
//     ]);

//     // Update the comment
//     $comment->comment = $request->input('comment'); // Use 'comment' field from request
//     $comment->save();

//     return response()->json(['message' => 'Comment updated successfully', 'comment' => $comment], 200);
// }

// // Delete a comment (only by the owner)
// public function deleteComment(Post $post, Comment $comment)
// {
//     // Ensure only the comment owner can delete the comment
//     if ($comment->user_id !== auth()->id()) {
//         return response()->json(['message' => 'Unauthorized'], 403);
//     }

//     // Delete the comment
//     $comment->delete();

//     return response()->json(['message' => 'Comment deleted successfully'], 200);
// }
// }