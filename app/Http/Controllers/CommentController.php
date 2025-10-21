<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;

/**
 * @group Comments
 *
 * APIs for managing post comments
 */
class CommentController extends Controller
{
    /**
     * Add a comment to a post.
     *
     * You can also reply to an existing comment by providing `parent_comment_id`.
     *
     * @authenticated
     *
     * Headers:
     * - Authorization: Bearer {token}
     *
     * @bodyParam post_id integer required The ID of the post. Example: 1
     * @bodyParam content string required The content of the comment. Example: "Nice post!"
     * @bodyParam parent_comment_id integer The ID of the parent comment if replying. Example: 5
     *
     * @response 200 {
     *  "message": "Comment added",
     *  "comment": {
     *    "id": 10,
     *    "post_id": 1,
     *    "user_id": 2,
     *    "content": "Nice post!",
     *    "parent_comment_id": null,
     *    "created_at": "2025-10-20T02:15:00.000000Z",
     *    "updated_at": "2025-10-20T02:15:00.000000Z"
     *  }
     * }
     *
     * @response 422 {
     *  "message": "The given data was invalid.",
     *  "errors": {
     *    "content": ["The content field is required."]
     *  }
     * }
     */
   public function store(Request $request)
{
    $validated = $request->validate([
        'post_id' => 'required|exists:posts,id',
        'content' => 'required|string',
        'parent_comment_id' => 'nullable|exists:comments,id'
    ]);

    $comment = Comment::create([
        'post_id' => $validated['post_id'],
        'user_id' => $request->user()->id,
        'content' => $validated['content'],
        'parent_comment_id' => $validated['parent_comment_id'] ?? null,
    ]);

    // Send notification to the post owner if commenter is not the owner
    $post = $comment->post;
    if ($request->user()->id !== $post->user_id) {
        $comment->notification()->create([
            'user_id' => $post->user_id,
            'type' => 'comment',
            'reference_id' => $comment->id,
            'is_read' => false,
        ]);
    }

    return response()->json(['message' => 'Comment added', 'comment' => $comment]);
}


    /**
     * Update a comment.
     *
     * Only the user who created the comment can update it.
     *
     * @authenticated
     *
     * Headers:
     * - Authorization: Bearer {token}
     *
     * @urlParam comment integer required The ID of the comment to update.
     * @bodyParam content string required The updated content of the comment. Example: "Updated comment!"
     *
     * @response 200 {
     *  "message": "Comment updated",
     *  "comment": {
     *    "id": 10,
     *    "post_id": 1,
     *    "user_id": 2,
     *    "content": "Updated comment!",
     *    "parent_comment_id": null,
     *    "created_at": "2025-10-20T02:15:00.000000Z",
     *    "updated_at": "2025-10-20T02:20:00.000000Z"
     *  }
     * }
     *
     * @response 403 {
     *  "message": "You do not have permission to update this comment."
     * }
     *
     * @response 422 {
     *  "message": "The given data was invalid.",
     *  "errors": {
     *    "content": ["The content field is required."]
     *  }
     * }
     */
    public function update(Request $request, Comment $comment)
    {
        if ($request->user()->id !== $comment->user_id) {
            return response()->json(['message' => 'You do not have permission to update this comment.'], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $comment->content = trim($validated['content']);
        $comment->save();

        return response()->json(['message' => 'Comment updated', 'comment' => $comment]);
    }

    /**
     * Delete a comment.
     *
     * Only the user who created the comment can delete it.
     *
     * @authenticated
     *
     * Headers:
     * - Authorization: Bearer {token}
     *
     * @urlParam comment integer required The ID of the comment to delete.
     *
     * @response 200 {
     *  "message": "Comment deleted"
     * }
     *
     * @response 403 {
     *  "message": "You do not have permission to delete this comment."
     * }
     *
     * @response 404 {
     *  "message": "No query results for model [Comment] ..."
     * }
     */
    public function destroy(Request $request, Comment $comment)
    {
        if ($request->user()->id !== $comment->user_id) {
            return response()->json(['message' => 'You do not have permission to delete this comment.'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted']);
    }
}
