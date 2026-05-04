<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SkillDrop\StoreSkillDropRequest;
use App\Http\Requests\SkillDrop\UpdateSkillDropRequest;
use App\Http\Resources\SkillDropResource;
use App\Models\SkillDrop;
use App\Services\SkillDropService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SkillDropController extends Controller
{
    public function __construct(
        protected SkillDropService $skillDropService
    ) {}

    public function index(Request $request)
    {
        $skillDrops = $this->skillDropService->getAll(
            $request->input('per_page', config('zumi.pagination.default_per_page', 15))
        );

        return SkillDropResource::collection($skillDrops);
    }

    public function library(Request $request)
    {
        $skillDrops = $this->skillDropService->getLibrary(
            $request->user(),
            $request->input('per_page', config('zumi.pagination.default_per_page', 15))
        );

        return SkillDropResource::collection($skillDrops);
    }

    public function myDrops(Request $request)
    {
        $skillDrops = $this->skillDropService->getByUser(
            $request->user(),
            $request->input('per_page', config('zumi.pagination.default_per_page', 15))
        );

        return SkillDropResource::collection($skillDrops);
    }

    public function store(StoreSkillDropRequest $request): JsonResponse
    {
        $skillDrop = $this->skillDropService->create($request->user(), $request->validated());

        return response()->json([
            'message'    => 'Skill Drop created successfully',
            'skill_drop' => new SkillDropResource($skillDrop),
        ], Response::HTTP_CREATED);
    }

    public function show(SkillDrop $skillDrop): SkillDropResource
    {
        return new SkillDropResource($skillDrop->load('user'));
    }

    public function update(UpdateSkillDropRequest $request, SkillDrop $skillDrop): JsonResponse
    {
        $this->authorize('update', $skillDrop);

        $updated = $this->skillDropService->update($skillDrop, $request->validated());

        return response()->json([
            'message'    => 'Skill Drop updated successfully',
            'skill_drop' => new SkillDropResource($updated),
        ]);
    }

    public function destroy(SkillDrop $skillDrop): JsonResponse
    {
        $this->authorize('delete', $skillDrop);

        $this->skillDropService->delete($skillDrop);

        return response()->json(['message' => 'Skill Drop deleted successfully']);
    }

    public function purchase(Request $request, SkillDrop $skillDrop): JsonResponse
    {
        $this->authorize('purchase', $skillDrop);

        $result = $this->skillDropService->purchase($request->user(), $skillDrop);

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], Response::HTTP_BAD_REQUEST);
        }

        return response()->json(['message' => 'Skill Drop purchased successfully']);
    }
}
