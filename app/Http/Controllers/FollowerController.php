<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Follower;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * @group Followers
 *
 * APIs for following, unfollowing, and viewing followers.
 */
class FollowerController extends Controller
{
    /**
     * Follow or unfollow another user.
     *
     * This endpoint allows the **authenticated user** to follow or unfollow another user.
     * You **do not need to send your own user ID** — it’s automatically detected from your token.
     *
     * **Behavior:**
     * - If you are not yet following the target user → it will follow them.
     * - If you are already following the target user → it will unfollow them.
     *
     * @authenticated
     *
     * @urlParam user integer required The ID of the user you want to follow or unfollow. Example: 5
     *
     * @response 200 {
     *  "message": "Followed"
     * }
     * @response 200 {
     *  "message": "Unfollowed"
     * }
     * @response 400 {
     *  "message": "You cannot follow yourself"
     * }
     */
    public function toggleFollow(Request $request, $user)
    {
        $follower = $request->user();
        $target = User::findOrFail($user);

        if ($follower->id === $target->id) {
            return response()->json(['message' => 'You cannot follow yourself'], 400);
        }

        $existing = Follower::where('follower_id', $follower->id)
            ->where('following_id', $target->id)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['message' => 'Unfollowed']);
        }

        Follower::create([
            'follower_id' => $follower->id,
            'following_id' => $target->id,
        ]);

        return response()->json(['message' => 'Followed']);
    }

    /**
     * Get all followers of the authenticated user.
     *
     * Returns a list of users who are following you.
     *
     * @authenticated
     *
     * @response 200 [
     *  {
     *      "id": 3,
     *      "name": "John Doe",
     *      "email": "john@example.com"
     *  }
     * ]
     */
    public function followers(Request $request)
    {
        $user = $request->user();

        $followers = Follower::where('following_id', $user->id)
            ->with('follower:id,name,email')
            ->get()
            ->pluck('follower');

        return response()->json($followers);
    }

    /**
     * Get all users that the authenticated user is following.
     *
     * Returns a list of users that **you** follow.
     *
     * @authenticated
     *
     * @response 200 [
     *  {
     *      "id": 5,
     *      "name": "Jane Smith",
     *      "email": "jane@example.com"
     *  }
     * ]
     */
    public function following(Request $request)
    {
        $user = $request->user();

        $following = Follower::where('follower_id', $user->id)
            ->with('following:id,name,email')
            ->get()
            ->pluck('following');

        return response()->json($following);
    }
}
