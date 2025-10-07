# Quvel Core

A Laravel package providing essential utilities for full-stack applications including device management, push notifications, platform detection, locale handling, distributed tracing, and more.

## Installation

```bash
composer require quvel/core
```

Publish configuration:
```bash
php artisan vendor:publish --tag=quvel-config
```

Publish migrations:
```bash
php artisan vendor:publish --tag=quvel-migrations
php artisan migrate
```

## Table of Contents

- [Actions](#actions)
- [Captcha](#captcha)
- [Device Management](#device-management)
- [Push Notifications](#push-notifications)
- [Platform Detection](#platform-detection)
- [Locale Management](#locale-management)
- [Logging](#logging)
- [Tracing](#tracing)
- [Public IDs](#public-ids)
- [Redirects](#redirects)
- [Configuration](#configuration)

---

## Actions

Actions are single-purpose classes that encapsulate business logic. They're framework-agnostic and can be used in controllers, jobs, commands, or anywhere else.

### Device Actions

**Register a device:**
```php
use Quvel\Core\Actions\RegisterDeviceAction;

app(RegisterDeviceAction::class)->execute([
    'device_id' => 'unique-device-id',
    'platform' => 'ios',
    'device_name' => 'John's iPhone',
    'push_token' => 'fcm-token-here',
    'push_provider' => 'fcm',
]);
```

**Update push token:**
```php
use Quvel\Core\Actions\UpdatePushTokenAction;

app(UpdatePushTokenAction::class)->execute(
    deviceId: 'device-123',
    pushToken: 'new-token',
    provider: 'fcm'
);
```

**Deactivate a device:**
```php
use Quvel\Core\Actions\DeactivateDeviceAction;

app(DeactivateDeviceAction::class)->execute(
    deviceId: 'device-123',
    reason: 'User logged out'
);
```

**Get user's devices:**
```php
use Quvel\Core\Actions\GetUserDevicesAction;

$devices = app(GetUserDevicesAction::class)->execute(auth()->id());
```

### Push Notification Actions

**Send push notification:**
```php
use Quvel\Core\Actions\SendPushNotificationAction;

$result = app(SendPushNotificationAction::class)->execute(
    title: 'New Message',
    body: 'You have a new message',
    data: ['message_id' => 123],
    userId: auth()->id(),
    scope: 'all_user_devices' // or 'requesting_device'
);
```

**Use in jobs:**
```php
class SendWelcomeNotification implements ShouldQueue
{
    public function __construct(
        private readonly SendPushNotificationAction $sendPush
    ) {}

    public function handle(): void
    {
        $this->sendPush->execute(
            title: 'Welcome!',
            body: 'Thanks for signing up',
            userId: $this->userId
        );
    }
}
```

---

## Captcha

Protect your endpoints from bots and automated attacks using Google reCAPTCHA.

### Configuration

```php
// config/quvel.php
'captcha' => [
    'enabled' => env('CAPTCHA_ENABLED', true),
    'driver' => env('CAPTCHA_DRIVER', \Quvel\Core\Captcha\GoogleRecaptchaDriver::class),
    'score_threshold' => env('RECAPTCHA_SCORE_THRESHOLD', 0.5), // reCAPTCHA v3
    'timeout' => env('CAPTCHA_TIMEOUT', 30),
],
```

```env
CAPTCHA_ENABLED=true
RECAPTCHA_SECRET_KEY=your-secret-key
RECAPTCHA_SITE_KEY=your-site-key
```

### Basic Usage

**Verify captcha programmatically:**
```php
use Quvel\Core\Facades\Captcha;

$result = Captcha::verify($token, $request->ip());

if ($result->isSuccessful()) {
    // Continue with request
}
```

**Protect routes with middleware:**
```php
Route::post('/register', function () {
    // Protected by captcha
})->middleware('captcha'); // Uses default field 'captcha_token'

// Custom input field
Route::post('/login', function () {
    // ...
})->middleware('captcha:recaptcha_response');
```

**Check reCAPTCHA v3 score:**
```php
$result = Captcha::verify($token);

if ($result->hasScore() && $result->score >= 0.5) {
    // High confidence user
}
```

### Custom Captcha Driver

Implement your own captcha provider:

```php
use Quvel\Core\Contracts\CaptchaDriverInterface;
use Quvel\Core\Captcha\CaptchaVerificationResult;

class HCaptchaDriver implements CaptchaDriverInterface
{
    public function verify(string $token, ?string $ip = null, ?string $action = null): CaptchaVerificationResult
    {
        // Your verification logic
        return CaptchaVerificationResult::success();
    }

    public function supportsScoring(): bool
    {
        return false;
    }

    public function getDefaultScoreThreshold(): ?float
    {
        return null;
    }
}
```

Set in config:
```php
'captcha' => [
    'driver' => \App\Captcha\HCaptchaDriver::class,
],
```

### Events

Listen for captcha events:

```php
use Quvel\Core\Events\CaptchaVerifySuccess;
use Quvel\Core\Events\CaptchaVerifyFailed;

Event::listen(CaptchaVerifyFailed::class, function ($event) {
    Log::warning('Captcha failed', [
        'ip' => $event->ipAddress,
        'reason' => $event->reason,
    ]);
});
```

---


## Device Management

Track user devices across web, mobile, and desktop platforms. Register devices, manage push tokens, and maintain device lifecycle.

### Configuration

```php
// config/quvel.php
'devices' => [
    'enabled' => env('DEVICES_ENABLED', true),
    'allow_anonymous' => env('DEVICES_ALLOW_ANONYMOUS', false),
    'cleanup_inactive_after_days' => env('DEVICES_CLEANUP_DAYS', 90),
    'max_devices_per_user' => env('DEVICES_MAX_PER_USER', 10),
],
```

### Basic Usage

**Using Actions (recommended):**
```php
use Quvel\Core\Actions\RegisterDeviceAction;

$device = app(RegisterDeviceAction::class)->execute([
    'device_id' => 'unique-device-id',
    'platform' => 'ios',
    'device_name' => 'John's iPhone',
    'push_token' => 'fcm-token',
    'push_provider' => 'fcm',
]);
```

**Using Facade:**
```php
use Quvel\Core\Facades\DeviceManager;

$device = DeviceManager::registerDevice([
    'device_id' => 'device-123',
    'platform' => 'android',
]);

// Update push token
DeviceManager::updatePushToken('device-123', 'new-token', 'fcm');

// Deactivate device
DeviceManager::deactivateDevice('device-123', 'User logged out');

// Get user's devices
$devices = DeviceManager::getUserDevices(auth()->id());
```

### Device Model

Query devices directly:

```php
use Quvel\Core\Models\UserDevice;

// Get active devices for a user
$devices = UserDevice::forUser($userId)->active()->get();

// Find by device ID
$device = UserDevice::where('device_id', 'device-123')->first();

// Check if device has valid push token
if ($device->hasValidPushToken()) {
    // Send notification
}

// Platform filtering
$iosDevices = UserDevice::forPlatform('ios')->get();
```

### API Routes

Publish and enable device routes:

```bash
php artisan vendor:publish --tag=quvel-routes
```

```php
// config/quvel.php
'routes' => [
    'devices' => [
        'enabled' => env('QUVEL_DEVICE_ROUTES_ENABLED', false),
        'prefix' => 'api/devices',
        'name' => 'devices.',
        'middleware' => ['api', 'auth:sanctum'],
    ],
],
```

Available endpoints:
- `POST /api/devices/register` - Register a device
- `POST /api/devices/push-token` - Update push token
- `POST /api/devices/deactivate` - Deactivate device
- `GET /api/devices/list` - List user's devices

### Middleware

Automatically detect and track devices:

```php
// Applied globally via config
'middleware' => [
    'groups' => [
        'api' => [
            'device-detection',
        ],
    ],
],
```

Access device in request:

```php
$device = $request->attributes->get('device');
$deviceId = $request->attributes->get('device_id');
```

### Events

Listen for device events:

```php
use Quvel\Core\Events\DeviceRegistered;
use Quvel\Core\Events\DeviceRemoved;

Event::listen(DeviceRegistered::class, function ($event) {
    Log::info('Device registered', [
        'device_id' => $event->deviceId,
        'platform' => $event->platform,
    ]);
});
```

### Cleanup Inactive Devices

Schedule device cleanup:

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        app(\Quvel\Core\Contracts\DeviceManager::class)->cleanupInactiveDevices();
    })->daily();
}
```

---

## Push Notifications

Send push notifications to user devices across multiple platforms (FCM, APNS, Web Push). Supports device targeting and batch processing.

### Configuration

```php
// config/quvel.php
'push' => [
    'enabled' => env('PUSH_ENABLED', true),
    'drivers' => explode(',', env('PUSH_DRIVERS', 'fcm,apns,web')),
    
    'fcm' => [
        'server_key' => env('FCM_SERVER_KEY'),
        'project_id' => env('FCM_PROJECT_ID'),
    ],
    
    'apns' => [
        'key_path' => env('APNS_KEY_PATH'),
        'key_id' => env('APNS_KEY_ID'),
        'team_id' => env('APNS_TEAM_ID'),
        'bundle_id' => env('APNS_BUNDLE_ID'),
        'environment' => env('APNS_ENVIRONMENT', 'sandbox'),
    ],
    
    'web_push' => [
        'vapid_subject' => env('VAPID_SUBJECT'),
        'vapid_public_key' => env('VAPID_PUBLIC_KEY'),
        'vapid_private_key' => env('VAPID_PRIVATE_KEY'),
    ],
    
    'batch_size' => env('PUSH_BATCH_SIZE', 1000),
],

'targeting' => [
    'default_scope' => env('TARGETING_DEFAULT_SCOPE', 'requesting_device'),
],
```

### Basic Usage

**Send to single device:**
```php
use Quvel\Core\Facades\PushNotification;

$success = PushNotification::sendToDevice(
    device: $device,
    title: 'New Message',
    body: 'You have a new message',
    data: ['message_id' => 123]
);
```

**Send to multiple devices:**
```php
$results = PushNotification::sendToDevices(
    devices: $devices,
    title: 'Update Available',
    body: 'A new version is available'
);

// Returns: ['device-123' => true, 'device-456' => false, ...]
```

**Using SendPushNotificationAction (recommended):**
```php
use Quvel\Core\Actions\SendPushNotificationAction;

$result = app(SendPushNotificationAction::class)->execute(
    title: 'Welcome!',
    body: 'Thanks for signing up',
    userId: auth()->id(),
    scope: 'all_user_devices'
);

// Returns:
// [
//     'success' => true,
//     'sent_count' => 3,
//     'total_count' => 3,
//     'results' => [...]
// ]
```

### Device Targeting

**Targeting scopes:**
```php
// Send only to the requesting device
$result = $sendPush->execute(
    title: 'Your order is ready',
    body: 'Pick up at counter 5',
    requestingDevice: $device,
    scope: 'requesting_device'
);

// Send to all user's devices
$result = $sendPush->execute(
    title: 'New login detected',
    body: 'Someone logged in from Chrome',
    userId: auth()->id(),
    scope: 'all_user_devices'
);
```

**Custom targeting:**

Extend the DeviceTargetingService for custom filtering:

```php
namespace App\Services;

use Quvel\Core\Targeting\DeviceTargetingService as BaseService;
use Illuminate\Support\Collection;

class CustomDeviceTargeting extends BaseService
{
    public function getTargetDevices($requestingDevice, $userId, $scope = null): Collection
    {
        $devices = parent::getTargetDevices($requestingDevice, $userId, $scope);
        
        // Add your custom filtering logic
        return $devices->filter(function ($device) {
            return $device->notification_preferences['enabled'] ?? true;
        });
    }
}
```

Bind in service provider:

```php
$this->app->bind(
    \Quvel\Core\Contracts\DeviceTargetingService::class,
    \App\Services\CustomDeviceTargeting::class
);
```

### Push Drivers

**Firebase Cloud Messaging (FCM):**

Supports Android, iOS, and Web platforms.

```env
FCM_SERVER_KEY=your-server-key
FCM_PROJECT_ID=your-project-id
```

**Apple Push Notification Service (APNS):**

Supports iOS and macOS.

```env
APNS_KEY_PATH=/path/to/AuthKey_XXXXXXXXXX.p8
APNS_KEY_ID=XXXXXXXXXX
APNS_TEAM_ID=XXXXXXXXXX
APNS_BUNDLE_ID=com.yourapp.bundle
APNS_ENVIRONMENT=production
```

**Web Push:**

Supports web and desktop browsers.

```env
VAPID_SUBJECT=mailto:your-email@example.com
VAPID_PUBLIC_KEY=your-public-key
VAPID_PRIVATE_KEY=your-private-key
```

### Custom Push Driver

Create a custom driver:

```php
use Quvel\Core\Contracts\PushDriver;
use Quvel\Core\Models\UserDevice;

class CustomPushDriver implements PushDriver
{
    public function getName(): string
    {
        return 'custom';
    }

    public function supports(string $platform): bool
    {
        return $platform === 'my-platform';
    }

    public function isConfigured(): bool
    {
        return !empty(config('services.custom_push.api_key'));
    }

    public function send(UserDevice $device, string $title, string $body, array $data = []): bool
    {
        // Your sending logic
        return true;
    }
}
```

Register in service provider:

```php
app(PushManager::class)->extend('custom', function () {
    return new CustomPushDriver();
});
```

### Events

Listen for push notification events:

```php
use Quvel\Core\Events\PushNotificationSent;
use Quvel\Core\Events\PushNotificationFailed;

Event::listen(PushNotificationSent::class, function ($event) {
    Log::info('Push sent', [
        'devices' => $event->deviceIds,
        'title' => $event->title,
    ]);
});

Event::listen(PushNotificationFailed::class, function ($event) {
    Log::error('Push failed', [
        'devices' => $event->deviceIds,
        'error' => $event->error,
    ]);
});
```

### Batch Processing

Large device lists are automatically batched:

```php
// Automatically processes in batches of 1000 (configurable)
$results = PushNotification::sendToDevices($thousands OfDevices, $title, $body);
```

Configure batch size:

```php
'push' => [
    'batch_size' => 500, // Process 500 devices at a time
],
```

---

## Platform Detection

Detect the platform (web, mobile, desktop) from which requests originate. Supports automatic detection from headers and provides platform-specific logic.

### Configuration

```php
// config/quvel.php
'headers' => [
    'platform' => env('HEADER_PLATFORM'), // Defaults to 'X-Platform'
],
```

### Usage

**Detect platform:**
```php
use Quvel\Core\Facades\Platform;

$platform = Platform::getPlatform(); // 'web', 'mobile', or 'desktop'

if (Platform::isPlatform('mobile')) {
    // Mobile-specific logic
}

if (Platform::supportsAppRedirects()) {
    // Platform supports deep links (mobile or desktop)
}
```

**Platform enum:**
```php
use Quvel\Core\Platform\PlatformType;

// Specific platform types
PlatformType::IOS->value;        // 'ios'
PlatformType::ANDROID->value;    // 'android'
PlatformType::ELECTRON->value;   // 'electron'

// Get main mode
$platform = PlatformType::tryFrom('ios');
$mode = $platform->getMainMode(); // 'mobile'

// Helper methods
$platform->isMobile();   // true for mobile platforms
$platform->isDesktop();  // true for desktop platforms
$platform->isApple();    // true for iOS/macOS
```

**Available platforms:**
- Web: `web`
- Mobile: `mobile`, `android`, `ios`, `capacitor`, `cordova`
- Desktop: `desktop`, `macos`, `windows`, `linux`, `electron`, `tauri`

### Middleware

Auto-detect platform on every request:

```php
// Applied globally via config
'middleware' => [
    'groups' => [
        'web' => ['platform-detection'],
        'api' => ['platform-detection'],
    ],
],
```

### Frontend Integration

Send platform from your frontend:

```js
// Capacitor
fetch('/api/endpoint', {
    headers: {
        'X-Platform': 'capacitor'
    }
});

// Electron
fetch('/api/endpoint', {
    headers: {
        'X-Platform': 'electron'
    }
});
```

---
