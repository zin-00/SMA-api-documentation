<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Notification;
use Illuminate\Http\Request;

/**
 * @group Messaging
 *
 * APIs for sending, retrieving, updating, and deleting messages
 */
class MessageController extends Controller
{
    /**
     * Retrieve all messages for the authenticated user.
     *
     * Retrieves messages where the authenticated user is either the sender or receiver,
     * including sender and receiver details.
     *
     * @authenticated
     *
     * Headers:
     * - Authorization: Bearer {token}
     *
     * @response 200 [
     *  {
     *      "id": 1,
     *      "sender_id": 1,
     *      "receiver_id": 2,
     *      "content": "Hello!",
     *      "created_at": "2025-10-20T12:00:00Z",
     *      "sender": {"id":1,"name":"Jane Doe"},
     *      "receiver": {"id":2,"name":"John Smith"}
     *  }
     * ]
     */
    public function index(Request $request)
    {
        return Message::where('sender_id', $request->user()->id)
            ->orWhere('receiver_id', $request->user()->id)
            ->with(['sender', 'receiver'])
            ->latest()
            ->get();
    }

    /**
     * Send a new message to another user.
     *
     * Allows the authenticated user to send a message to a specific user.
     *
     * @authenticated
     *
     * Headers:
     * - Authorization: Bearer {token}
     *
     * @bodyParam receiver_id integer required The ID of the user receiving the message. Example: 2
     * @bodyParam content string required The content of the message. Example: "Hello, how are you?"
     *
     * @response 200 {
     *  "message": "Message sent",
     *  "data": {
     *      "id": 1,
     *      "sender_id": 1,
     *      "receiver_id": 2,
     *      "content": "Hello, how are you?",
     *      "created_at": "2025-10-20T12:00:00Z"
     *  }
     * }
     *
     * @response 422 {
     *  "message": "The given data was invalid.",
     *  "errors": {
     *      "receiver_id": ["The selected receiver id is invalid."],
     *      "content": ["The content field is required."]
     *  }
     * }
     */
    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'content' => 'required|string',
        ]);

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $request->receiver_id,
            'content' => $request->content,
        ]);
            // Create a notification for the receiver
        Notification::create([
            'user_id' => $request->receiver_id,
            'type' => 'message',
            'reference_id' => $message->id,
            'is_read' => false,
        ]);

        return response()->json(['message' => 'Message sent', 'data' => $message]);
    }

    /**
     * Update a message.
     *
     * Allows the sender of a message to update its content.
     *
     * @authenticated
     *
     * Headers:
     * - Authorization: Bearer {token}
     *
     * @urlParam message integer required The ID of the message to update.
     *
     * @bodyParam content string required The updated message content. Example: "Updated message"
     *
     * @response 200 {
     *  "message": "Message updated",
     *  "data": {
     *      "id": 1,
     *      "sender_id": 1,
     *      "receiver_id": 2,
     *      "content": "Updated message",
     *      "created_at": "2025-10-20T12:00:00Z"
     *  }
     * }
     *
     * @response 403 {
     *  "message": "You do not have permission to update this message."
     * }
     *
     * @response 422 {
     *  "message": "The given data was invalid.",
     *  "errors": {
     *      "content": ["The content field is required."]
     *  }
     * }
     */
    public function update(Request $request, Message $message)
    {
        if ($request->user()->id !== $message->sender_id) {
            return response()->json(['message' => 'You do not have permission to update this message.'], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $message->content = trim($validated['content']);
        $message->save();

        return response()->json(['message' => 'Message updated', 'data' => $message]);
    }

    /**
     * Delete a message.
     *
     * Allows the sender of a message to delete it.
     *
     * @authenticated
     *
     * Headers:
     * - Authorization: Bearer {token}
     *
     * @urlParam message integer required The ID of the message to delete.
     *
     * @response 200 {
     *  "message": "Message deleted"
     * }
     *
     * @response 403 {
     *  "message": "You do not have permission to delete this message."
     * }
     */
    public function destroy(Request $request, Message $message)
    {
        if ($request->user()->id !== $message->sender_id) {
            return response()->json(['message' => 'You do not have permission to delete this message.'], 403);
        }

        $message->delete();

        return response()->json(['message' => 'Message deleted']);
    }
}
