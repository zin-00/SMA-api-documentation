<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

/**
 * @group Comments
 *
 * APIs for creating, updating, and deleting comments on posts.
 */
class CommentController extends Controller
{
    /**
     * Add a comment to a post.
     *
     * The authenticated user can comment on a post or reply to another comment.
     *
     * You **don’t need to send your user ID** — it’s automatically taken from your access token.
     *
     * @authenticated
     *
     * @bodyParam post_id integer required The ID of the post to comment on. Example: 1
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
     *    "parent_comment_id": null
     *  }
     * }
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'post_id' => 'required|exists:posts,id',
            'content' => 'required|string|max:2000',
            'parent_comment_id' => 'nullable|exists:comments,id'
        ]);

        $comment = Comment::create([
            'post_id' => $validated['post_id'],
            'user_id' => $request->user()->id,
            'content' => trim($validated['content']),
            'parent_comment_id' => $validated['parent_comment_id'] ?? null,
        ]);

        return response()->json([
            'message' => 'Comment added',
            'comment' => $comment
        ]);
    }

    /**
     * Update a comment.
     *
     * Only the user who created the comment can update it.
     *
     * @authenticated
     *
     * @urlParam comment_id integer required The ID of the comment to update. Example: 10
     * @bodyParam content string required The updated content. Example: "Updated comment!"
     *
     * @response 200 {
     *  "message": "Comment updated",
     *  "comment": {
     *    "id": 10,
     *    "content": "Updated comment!"
     *  }
     * }
     */
    public function update(Request $request, $comment_id)
    {
        $comment = Comment::findOrFail($comment_id);

        if ($request->user()->id !== $comment->user_id) {
            return response()->json([
                'message' => 'You do not have permission to update this comment.'
            ], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $comment->update(['content' => trim($validated['content'])]);

        return response()->json([
            'message' => 'Comment updated',
            'comment' => $comment
        ]);
    }

    /**
     * Delete a comment.
     *
     * Only the user who created the comment can delete it.
     *
     * @authenticated
     *
     * @urlParam comment_id integer required The ID of the comment to delete. Example: 10
     *
     * @response 200 {
     *  "message": "Comment deleted"
     * }
     */
    public function destroy(Request $request, $comment_id)
    {
        $comment = Comment::findOrFail($comment_id);

        if ($request->user()->id !== $comment->user_id) {
            return response()->json([
                'message' => 'You do not have permission to delete this comment.'
            ], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted']);
    }
}
