<?php

namespace App\Http\Controllers;

use App\Models\Friend;
use Illuminate\Http\Request;

/**
 * @group Friends
 *
 * APIs for managing friendships
 */
class FriendController extends Controller
{
    /**
     * List incoming friend requests.
     *
     * Returns all friend requests where the authenticated user is the recipient
     * and the status is "pending".
     *
     * @authenticated
     *
     * @response 200 [
     *  {
     *    "id": 5,
     *    "user_id": 2,
     *    "friend_id": 1,
     *    "status": "pending",
     *    "created_at": "2025-10-20T05:00:00.000000Z",
     *    "updated_at": "2025-10-20T05:00:00.000000Z",
     *    "sender": {
     *        "id": 2,
     *        "name": "Jane Doe"
     *    }
     *  }
     * ]
     */
    public function friendRequests(Request $request)
    {
        $user = $request->user();

        $requests = Friend::with('sender')
            ->where('friend_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->get();

        return response()->json($requests);
    }

    /**
     * Send a friend request.
     *
     * @authenticated
     *
     * @bodyParam friend_id integer required The ID of the user to send a request to. Example: 2
     *
     * @response 200 {
     *  "message": "Friend request sent"
     * }
     */
public function sendRequest(Request $request)
{
    $request->validate(['friend_id' => 'required|exists:users,id']);

    $user = $request->user();
    $friendId = $request->friend_id;

    if ($user->id === $friendId) {
        return response()->json(['message' => 'You cannot send a friend request to yourself'], 400);
    }

    $existing = Friend::where('user_id', $user->id)
        ->where('friend_id', $friendId)
        ->first();

    if ($existing) {
        return response()->json(['message' => 'Friend request already exists'], 400);
    }

    // Create friend request
    $friendRequest = Friend::create([
        'user_id' => $user->id,
        'friend_id' => $friendId,
        'status' => 'pending'
    ]);

    // Create notification for the receiver
    $friendRequest->notifications()->create([
        'user_id' => $friendId,
        'type' => 'friend_request',
        'reference_id' => $friendRequest->id,
        'is_read' => false,
    ]);

    return response()->json(['message' => 'Friend request sent']);
}

    /**
     * Accept a friend request.
     *
     * @authenticated
     *
     * @bodyParam request_id integer required The ID of the friend request. Example: 5
     *
     * @response 200 {
     *  "message": "Friend request accepted"
     * }
     */
    public function acceptRequest(Request $request)
    {
        $request->validate(['request_id' => 'required|exists:friends,id']);

        $friendRequest = Friend::findOrFail($request->request_id);

        if ($friendRequest->friend_id !== $request->user()->id) {
            return response()->json(['message' => 'You cannot accept this request'], 403);
        }

        $friendRequest->update(['status' => 'accepted']);

        // Create reciprocal record
        Friend::firstOrCreate(
            ['user_id' => $friendRequest->friend_id, 'friend_id' => $friendRequest->user_id],
            ['status' => 'accepted']
        );

        return response()->json(['message' => 'Friend request accepted']);
    }

    /**
     * Unfriend a user.
     *
     * @authenticated
     *
     * @bodyParam friend_id integer required The ID of the user to unfriend. Example: 3
     *
     * @response 200 {
     *  "message": "Unfriended successfully"
     * }
     */
    public function unfriend(Request $request)
    {
        $request->validate(['friend_id' => 'required|exists:users,id']);

        $user = $request->user();

        Friend::where(function ($q) use ($user, $request) {
            $q->where('user_id', $user->id)->where('friend_id', $request->friend_id);
        })->orWhere(function ($q) use ($user, $request) {
            $q->where('friend_id', $user->id)->where('user_id', $request->friend_id);
        })->delete();

        return response()->json(['message' => 'Unfriended successfully']);
    }

    /**
     * Block a user.
     *
     * @authenticated
     *
     * @bodyParam friend_id integer required The ID of the user to block. Example: 4
     *
     * @response 200 {
     *  "message": "User blocked"
     * }
     */
    public function block(Request $request)
    {
        $request->validate(['friend_id' => 'required|exists:users,id']);

        $user = $request->user();

        Friend::updateOrCreate(
            ['user_id' => $user->id, 'friend_id' => $request->friend_id],
            ['status' => 'blocked']
        );

        return response()->json(['message' => 'User blocked']);
    }

    /**
     * Restrict a user.
     *
     * @authenticated
     *
     * @bodyParam friend_id integer required The ID of the user to restrict. Example: 5
     *
     * @response 200 {
     *  "message": "User restricted"
     * }
     */
    public function restrict(Request $request)
    {
        $request->validate(['friend_id' => 'required|exists:users,id']);

        $user = $request->user();

        Friend::updateOrCreate(
            ['user_id' => $user->id, 'friend_id' => $request->friend_id],
            ['status' => 'restricted']
        );

        return response()->json(['message' => 'User restricted']);
    }
}
