<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Follower;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * @group Followers
 *
 * APIs for following and unfollowing users
 */
class FollowerController extends Controller
{
    /**
     * Toggle following a user.
     *
     * If the authenticated user is not already following the target user, this endpoint will follow them.
     * If already following, it will unfollow the user.
     *
     * @authenticated
     *
     * Headers:
     * - Authorization: Bearer {token}
     *
     * @urlParam user integer required The ID of the user to follow or unfollow.
     *
     * @response 200 {
     *  "message": "Followed"
     * }
     *
     * @response 200 {
     *  "message": "Unfollowed"
     * }
     *
     * @response 400 {
     *  "message": "You cannot follow yourself"
     * }
     *
     * @response 404 {
     *  "message": "No query results for model [User] ..."
     * }
     */
    public function toggleFollow(User $user, Request $request)
    {
        $follower = $request->user();

        if ($follower->id === $user->id) {
            return response()->json(['message' => 'You cannot follow yourself'], 400);
        }

        $existing = Follower::where('follower_id', $follower->id)
            ->where('following_id', $user->id)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['message' => 'Unfollowed']);
        }

        Follower::create([
            'follower_id' => $follower->id,
            'following_id' => $user->id,
        ]);

        return response()->json(['message' => 'Followed']);
    }
}
