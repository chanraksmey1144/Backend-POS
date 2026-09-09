<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(protected SettingsService $settingsService)
    {
    }
    /**
     * Get all settings as a key-value object
     * GET /api/settings
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->settingsService->getAll(),
        ]);
    }
    /**
     * Get a specific setting by key
     * GET /api/settings/{key}
     */
    public function show(string $key): JsonResponse
    {
        $value = $this->settingsService->get($key);
        if ($value === null) {
            return response()->json([
                'success' => false,
                'message' => "Setting '{$key}' not found.",
            ], 404);
        }
        return response()->json([
            'success'     => true,
            'setting_key' => $key,
            'value'       => $value,
        ]);
    }
    /**
     * Batch update settings (e.g. from any of the 7 Settings tabs)
     * POST|PUT /api/settings
     */
    public function update(Request $request): JsonResponse
    {
        $payload = $request->except(['_method', '_token']);
        if (empty($payload)) {
            return response()->json([
                'success' => false,
                'message' => 'No settings data provided.',
            ], 422);
        }
        $updated = $this->settingsService->updateBatch($payload);
        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully.',
            'data'    => $updated,
        ]);
    }
}
