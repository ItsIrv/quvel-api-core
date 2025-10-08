<?php

declare(strict_types=1);

namespace Quvel\Core\Actions;

use Quvel\Core\Contracts\DeviceTargetingService;
use Quvel\Core\Contracts\PushManager;
use Quvel\Core\Models\UserDevice;

class SendPushNotificationAction
{
    public function __construct(
        private readonly PushManager $pushManager,
        private readonly DeviceTargetingService $targetingService
    ) {}

    public function __invoke(
        string $title,
        string $body,
        array $data = [],
        ?UserDevice $requestingDevice = null,
        ?int $userId = null,
        ?string $scope = null
    ): array {
        $targetDevices = $this->targetingService->getTargetDevices(
            $requestingDevice,
            $userId,
            $scope
        );

        if ($targetDevices->isEmpty()) {
            return ['success' => false, 'message' => 'No target devices found'];
        }

        $results = $this->pushManager->sendToDevices($targetDevices, $title, $body, $data);

        $successCount = array_sum($results);
        $totalCount = count($results);

        return [
            'success' => $successCount > 0,
            'sent_count' => $successCount,
            'total_count' => $totalCount,
            'results' => $results,
        ];
    }
}