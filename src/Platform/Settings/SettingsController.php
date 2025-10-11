<?php

declare(strict_types=1);

namespace Quvel\Core\Platform\Settings;

use Illuminate\Http\JsonResponse;
use Quvel\Core\Facades\PlatformSettings;

/**
 * Controller for platform settings endpoint.
 * Provides server-driven configuration for clients.
 */
class SettingsController
{
    /**
     * Get platform settings for the requesting client.
     *
     * @return JsonResponse Settings for the detected platform
     */
    public function index(): JsonResponse
    {
        $settings = PlatformSettings::getCurrentPlatformSettings();

        return response()->json([
            'success' => true,
            'settings' => $settings,
        ]);
    }
}
