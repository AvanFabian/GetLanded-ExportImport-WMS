<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AlertService;
use Illuminate\Http\JsonResponse;

class AlertController extends Controller
{
    protected $alertService;

    public function __construct(AlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    public function index(): JsonResponse
    {
        $alerts = $this->alertService->getAlerts();

        return response()->json([
            'count' => $alerts->count(),
            'alerts' => $alerts,
        ]);
    }
}
