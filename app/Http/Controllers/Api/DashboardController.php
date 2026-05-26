<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardCacheService;
use Illuminate\Http\JsonResponse;

/**
 * Thin controller for dashboard statistics.
 */
class DashboardController extends Controller
{
    public function __construct(
        protected DashboardCacheService $cacheService,
    ) {}

    /**
     * Return cached dashboard statistics and trending tags.
     */
    public function index(): JsonResponse
    {
        $data = $this->cacheService->getDashboardData();

        return response()->json($data);
    }
}
