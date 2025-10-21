<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Notification;
use App\Models\Post;
use Illuminate\Http\Request;

/**
 * @group Likes
 *
 * APIs for liking and unliking posts
 */
class LikeController extends Controller
{
    /**
     * Toggle like for a post.
     *
     * If the user has already liked the post, it will be unliked. Otherwise, it will be liked.
     *
     * @authenticated
     *
     * Headers:
     * - Authorization: Bearer {token}
     *
     * @urlParam post integer required The ID of the post to like or unlike. Example: 1
     *
     * @response 200 {
     *  "message": "Liked"
     * }
     *
     * @response 200 {
     *  "message": "Unliked"
     * }
     *
     * @response 404 {
     *  "message": "No query results for model [Post] ..."
     * }
     */
     public function toggleLike(Post $post, Request $request)
    {
        $user = $request->user();

        $like = Like::where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->first();

        if ($like) {
            $like->delete();

            // Optional: delete the like notification
            Notification::where('type', 'like')
                ->where('reference_id', $post->id)
                ->where('user_id', $post->user_id)
                ->delete();

            return response()->json(['message' => 'Unliked']);
        }

        Like::create([
            'post_id' => $post->id,
            'user_id' => $user->id
        ]);

        // Create a notification for the post owner if it's not the same user
        if ($user->id !== $post->user_id) {
    $like = Like::where('post_id', $post->id)->where('user_id', $user->id)->first();

            $like->notification()->create([
                'user_id' => $post->user_id,
                'type' => 'like',
                'reference_id' => $post->id,
                'is_read' => false
            ]);
        }

        return response()->json(['message' => 'Liked']);
    }
}
