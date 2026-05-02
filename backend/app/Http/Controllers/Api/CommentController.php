<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Wave;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CommentController extends Controller
{
    public function __construct(
        protected CommentService $commentService
    ) {}

    /**
     * Get comments for a wave.
     */
    public function forWave(Wave $wave): JsonResponse
    {
        $comments = $this->commentService->getComments($wave);
        return response()->json($comments);
    }

    /**
     * Store a comment for a wave.
     */
    public function storeWave(Request $request, Wave $wave): JsonResponse
    {
        $request->validate([
            'content'   => 'required|string|max:1000',
            'parent_id' => 'nullable|uuid|exists:comments,id',
        ]);

        $comment = $this->commentService->addComment(
            $request->user(),
            $wave,
            $request->content,
            $request->parent_id
        );

        return response()->json($comment, Response::HTTP_CREATED);
    }

    /**
     * Delete a comment.
     */
    public function destroy(Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);

        $this->commentService->deleteComment($comment);

        return response()->json([
            'message' => 'Comment deleted successfully.',
        ], Response::HTTP_OK);
    }
}
