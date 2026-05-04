<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreReportRequest;
use App\Http\Resources\ReportResource;
use App\Services\ModerationService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function __construct(
        protected ModerationService $moderationService
    ) {}

    /**
     * Store a new report.
     */
    public function store(StoreReportRequest $request): JsonResponse
    {
        $report = $this->moderationService->createReport(
            $request->user(),
            $request->validated('reported_type'),
            $request->validated('reported_id'),
            $request->validated('reason'),
            $request->validated('description')
        );

        return response()->json([
            'message' => 'Report submitted successfully.',
            'data'    => new ReportResource($report),
        ], Response::HTTP_CREATED);
    }
}
