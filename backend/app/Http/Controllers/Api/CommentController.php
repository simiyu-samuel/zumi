<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Resources\CommentResource;
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
    public function forWave(Request $request, Wave $wave): JsonResponse
    {
        $comments = $this->commentService->getComments(
            $wave, 
            $request->input('per_page', config('zumi.pagination.default_per_page', 15))
        );
        
        return response()->json(CommentResource::collection($comments)->response()->getData(true));
    }

    /**
     * Store a comment for a wave.
     */
    public function storeWave(StoreCommentRequest $request, Wave $wave): JsonResponse
    {
        $comment = $this->commentService->addComment(
            $request->user(),
            $wave,
            $request->content,
            $request->parent_id
        );

        return response()->json(new CommentResource($comment), Response::HTTP_CREATED);
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

    /**
     * Like/Unlike a comment.
     */
    public function like(Request $request, Comment $comment): JsonResponse
    {
        $liked = $this->commentService->toggleLike($request->user(), $comment);

        return response()->json([
            'liked' => $liked,
            'likes_count' => $comment->fresh()->likes_count,
        ]);
    }
}
