<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @group Notifications
 *
 * APIs for viewing, marking, and deleting notifications
 */
class NotificationController extends Controller
{
    /**
     * Retrieve all notifications for the authenticated user.
     *
     * Returns the list of notifications for the authenticated user, ordered from newest to oldest.
     *
     * @authenticated
     *
     * Headers:
     * - Authorization: Bearer {token}
     *
     * @response 200 [
     *  {
     *      "id": 1,
     *      "type": "App\\Notifications\\PostLiked",
     *      "data": {"post_id": 10, "message": "Someone liked your post"},
     *      "is_read": false,
     *      "created_at": "2025-10-20T12:00:00Z"
     *  }
     * ]
     */
    public function index(Request $request)
    {
        return $request->user()->notifications()->latest()->get();
    }

    /**
     * Mark a notification as read.
     *
     * Marks the notification with the specified ID as read for the authenticated user.
     *
     * @authenticated
     *
     * Headers:
     * - Authorization: Bearer {token}
     *
     * @urlParam id integer required The ID of the notification.
     *
     * @response 200 {
     *  "message": "Notification marked as read"
     * }
     *
     * @response 404 {
     *  "message": "No query results for model [Notification] ..."
     * }
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->update(['is_read' => true]);

        return response()->json(['message' => 'Notification marked as read']);
    }

    /**
     * Delete a notification.
     *
     * Deletes the notification with the specified ID for the authenticated user.
     *
     * @authenticated
     *
     * Headers:
     * - Authorization: Bearer {token}
     *
     * @urlParam id integer required The ID of the notification.
     *
     * @response 200 {
     *  "message": "Notification deleted"
     * }
     *
     * @response 404 {
     *  "message": "No query results for model [Notification] ..."
     * }
     */
    public function destroy(Request $request, $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->delete();

        return response()->json(['message' => 'Notification deleted']);
    }
}
