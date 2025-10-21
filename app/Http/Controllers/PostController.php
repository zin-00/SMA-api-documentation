<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;

/**
 * @group Posts
 *
 * APIs for managing user posts
 */
class PostController extends Controller
{
    /**
     * Display all posts.
    *
    * @authenticated
    *
    * Headers:
    * - Authorization: Bearer {token}
    *
    * @response 200 [
    *  {
    *    "id": 1,
    *    "content": "Hello world",
    *    "image_url": null,
    *    "video_url": null,
    *    "privacy": "public",
    *    "user": {"id":1, "name":"Jane Doe"},
    *    "comments": []
    *  }
    * ]
     */
    public function index()
    {
        return Post::with('user', 'comments')->latest()->get();
    }

    /**
     * Store a new post.
     *
    * @authenticated
    *
    * Headers:
    * - Authorization: Bearer {token}
    *
    * @bodyParam content string The post text. Example: "New post"
    * @bodyParam image_url string The post image URL. Example: "https://example.com/image.jpg"
    * @bodyParam video_url string The post video URL. Example: "https://example.com/video.mp4"
    * @bodyParam privacy string The privacy level (public/friends/private). Example: "public"
    *
    * @response 200 {
    *  "message": "Post created successfully",
    *  "post": {
    *    "id": 10,
    *    "content": "New post",
    *    "image_url": "https://example.com/image.jpg",
    *    "video_url": null,
    *    "privacy": "public",
    *    "user_id": 1
    *  }
    * }
    *
    * @response 422 {
    *  "message": "The given data was invalid.",
    *  "errors": {
    *    "privacy": ["The selected privacy is invalid."]
    *  }
    * }
    *
    * @example request {
    *  "content": "New post",
    *  "image_url": "https://example.com/image.jpg",
    *  "privacy": "public"
    * }
     */
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'nullable|string',
            'image_url' => 'nullable|string',
            'video_url' => 'nullable|string',
            'privacy' => 'in:public,friends,private',
        ]);

        $post = $request->user()->posts()->create($request->all());

        return response()->json(['message' => 'Post created successfully', 'post' => $post]);
    }

    /**
     * Show a specific post.
    *
    * @authenticated
    *
    * Headers:
    * - Authorization: Bearer {token}
    *
    * @response 200 {
    *  "id": 1,
    *  "content": "Hello world",
    *  "user": {"id":1, "name":"Jane Doe"},
    *  "comments": []
    * }
     */
    public function show(Post $post)
    {
        return $post->load('user', 'comments');
    }

    /**
     * Update a post.
     *
    * @authenticated
    *
    * Headers:
    * - Authorization: Bearer {token}
    *
    * @bodyParam content string The post text.
    * @bodyParam image_url string The post image URL.
    * @bodyParam video_url string The post video URL.
    * @bodyParam privacy string The privacy level (public/friends/private).
    *
    * @response 200 {
    *  "message": "Post updated",
    *  "post": { "id": 1, "content": "Updated content" }
    * }
     */
    public function update(Request $request, $id)
{
    $post = Post::findOrFail($id);
    // Only allow owner to update
    if ($request->user()->id !== $post->user_id) {
        return response()->json(['message' => 'You do not have permission to update this post.'], 403);
    }
    $validated = $request->validate([
        'content' => 'nullable|string',
        'image_url' => 'nullable|string',
        'video_url' => 'nullable|string',
        'privacy' => 'in:public,friends,private',
    ]);
    $post->update($validated);

    return response()->json([
        'message' => 'Post updated successfully',
        'post' => $post
    ]);
}


    /**
     * Delete a post.
     *
    * @authenticated
    *
    * Headers:
    * - Authorization: Bearer {token}
    *
    * @response 200 {
    *  "message": "Post deleted"
    * }
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        // Only allow owner to delete
        if (request()->user()->id !== $post->user_id) {
            return response()->json(['message' => 'You do not have permission to delete this post.'], 403);
        }
        $post->delete();

        return response()->json(['message' => 'Post deleted']);
    }
}
