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

- [Captcha](#captcha)
- [Device Management](#device-management)
- [Push Notifications](#push-notifications)
- [Platform Detection](#platform-detection)
- [Locale Management](#locale-management)
- [Distributed Tracing](#distributed-tracing)
- [Logging](#logging)
- [Public IDs](#public-ids)
- [Redirects](#redirects)
- [Security](#security)
- [Middleware](#middleware)
- [Publishing Assets](#publishing-assets)

---

## Captcha

Protect your endpoints from bots and automated attacks using Google reCAPTCHA.

### Configuration

```php
// config/quvel.php
'captcha' => [
    'enabled' => env('CAPTCHA_ENABLED', true),
    'driver' => env('CAPTCHA_DRIVER', \Quvel\Core\Captcha\GoogleRecaptchaDriver::class),
    'score_threshold' => env('RECAPTCHA_SCORE_THRESHOLD', 0.5),
    'timeout' => env('CAPTCHA_TIMEOUT', 30),
],
```

```env
CAPTCHA_ENABLED=true
RECAPTCHA_SECRET_KEY=your-secret-key
RECAPTCHA_SITE_KEY=your-site-key
```

### Usage

**Verify captcha:**
```php
use Quvel\Core\Facades\Captcha;

$result = Captcha::verify($token, $request->ip());

if ($result->isSuccessful()) {
    // Continue with request
}

// Check reCAPTCHA v3 score
if ($result->hasScore() && $result->score >= 0.5) {
    // High confidence user
}
```

**Protect routes with middleware:**
```php
Route::post('/register', function () {
    // Protected by captcha
})->middleware('captcha');

// Custom input field
Route::post('/login', function () {
    // ...
})->middleware('captcha:recaptcha_response');
```

### Custom Captcha Driver

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

### Usage

**Register a device:**

```php
use Quvel\Core\Facades\Device;

$device = Device::registerDevice([
    'device_id' => 'device-123',
    'platform' => 'ios',
    'device_name' => 'John's iPhone',
    'push_token' => 'fcm-token',
    'push_provider' => 'fcm',
]);
```

**Manage devices:**
```php
// Update push token
DeviceManager::updatePushToken('device-123', 'new-token', 'fcm');

// Deactivate device
DeviceManager::deactivateDevice('device-123', 'User logged out');

// Get user's devices
$devices = DeviceManager::getUserDevices(auth()->id());
```

### Device Model

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
$device = $request->attributes->get('device');
$deviceId = $request->attributes->get('device_id');
```

### Events

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

### Usage

**Send to device:**
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

### Device Targeting

**Get target devices:**
```php
use Quvel\Core\Facades\Targeting;

// Get devices for targeting scope
$devices = Targeting::getTargetDevices(
    requestingDevice: $device,
    userId: auth()->id(),
    scope: 'all_user_devices' // or 'requesting_device'
);

// Then send to those devices
PushNotification::sendToDevices($devices, $title, $body);
```

**Targeting scopes:**
- `requesting_device` - Only the device making the request
- `all_user_devices` - All of the user's active devices

### Custom Push Driver

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

```php
use Quvel\Core\Events\PushNotificationSent;
use Quvel\Core\Events\PushNotificationFailed;

Event::listen(PushNotificationSent::class, function ($event) {
    Log::info('Push sent', [
        'devices' => $event->deviceIds,
        'title' => $event->title,
    ]);
});
```

---

## Platform Detection

Detect the platform (web, mobile, desktop) from which requests originate.

### Configuration

```php
// config/quvel.php
'headers' => [
    'platform' => env('HEADER_PLATFORM'), // Defaults to 'X-Platform'
],
```

### Usage

```php
use Quvel\Core\Facades\PlatformDetector;

$platform = Platform::getPlatform(); // 'web', 'mobile', or 'desktop'

if (Platform::isPlatform('mobile')) {
    // Mobile-specific logic
}
```

**Platform tags:**
```php
use Quvel\Core\Platform\PlatformTag;

PlatformTag::IOS->value;        // 'ios'
PlatformTag::ANDROID->value;    // 'android'
PlatformTag::ELECTRON->value;   // 'electron'
PlatformTag::TABLET->value;     // 'tablet'
PlatformTag::SCREEN_LG->value;  // 'screen:lg'

$tag = PlatformTag::tryFrom('ios');
$mode = $tag->getMainMode(); // 'mobile'
$category = $tag->getCategory(); // 'os'
```

**Available platform tags:**
- Runtime: `web`, `capacitor`, `cordova`, `electron`, `tauri`
- OS: `ios`, `android`, `macos`, `windows`, `linux`
- Form Factor: `mobile`, `tablet`, `desktop`
- Screen Sizes: `screen:xs`, `screen:sm`, `screen:md`, `screen:lg`, `screen:xl`

### Frontend Integration

```js
// Multi-tag platform detection (comma-separated)
// iPhone in Capacitor
fetch('/api/endpoint', {
    headers: {
        'X-Platform': 'capacitor,ios,mobile,screen:sm'
    }
});

// iPad in Safari
fetch('/api/endpoint', {
    headers: {
        'X-Platform': 'web,ios,tablet,screen:lg'
    }
});

// Electron on macOS
fetch('/api/endpoint', {
    headers: {
        'X-Platform': 'electron,macos,desktop,screen:xl'
    }
});
```

---

## Locale Management

Automatic locale detection and management from request headers.

### Configuration

```php
// config/quvel.php
'locale' => [
    'allowed_locales' => explode(',', env('LOCALE_ALLOWED', 'en')),
    'fallback_locale' => env('LOCALE_FALLBACK', 'en'),
    'normalize_locales' => env('LOCALE_NORMALIZE', true), // en-US -> en
],
```

### Usage

```php
use Quvel\Core\Facades\Locale;

// Detect locale from request
$locale = $request->header('Accept-Language');

// Middleware automatically detects and sets locale
// Access via Laravel's app()->getLocale()
```

Locale is detected from:
1. `Accept-Language` header
2. Falls back to configured default

---

## Distributed Tracing

Track requests across your distributed system with automatic trace ID generation and propagation.

### Configuration

```php
// config/quvel.php
'tracing' => [
    'enabled' => env('TRACING_ENABLED', true),
    'accept_external_trace_ids' => env('TRACING_ACCEPT_EXTERNAL', true),
],

'headers' => [
    'trace_id' => env('HEADER_TRACE_ID'), // Defaults to 'X-Trace-ID'
],
```

### Usage

```php
use Quvel\Core\Facades\Trace;

// Middleware automatically generates trace IDs
// Access from Laravel's Context
use Illuminate\Support\Facades\Context;

$traceId = Context::get('trace_id');
```

### Logging with Trace Context

```php
use Quvel\Core\Logs\ContextualLogger;

$logger = new ContextualLogger(['user_id' => auth()->id()]);

$logger->info('Action performed', ['action' => 'update_profile']);

// Automatically includes trace_id in log context
```

### Frontend Integration

```js
const traceId = generateUUID();

fetch('/api/endpoint', {
    headers: {
        'X-Trace-ID': traceId
    }
});
```

---

## Logging

Enhanced logging with automatic sanitization of sensitive data.

### Contextual Logging

```php
use Quvel\Core\Logs\ContextualLogger;

$logger = new ContextualLogger(['user_id' => auth()->id()]);

$logger->info('Action performed', ['action' => 'update_profile']);

// Output includes trace_id, user_id, and context automatically
```

### Sanitized Logging

```php
use Quvel\Core\Logs\SanitizedContext;

$context = [
    'email' => 'user@example.com',
    'password' => 'secret123',
    'api_key' => 'sk_live_123',
];

$sanitized = SanitizedContext::sanitize($context);

// Result:
// [
//     'email' => 'example.com',  // domain_only
//     // password removed
//     'api_key' => 'sha256:...'  // hashed
// ]
```

### Configuration

```php
// config/quvel.php
'logging' => [
    'sanitization_rules' => [
        'password' => 'remove',
        'token' => 'hash',
        'email' => 'domain_only',
        'credit_card' => 'mask',
    ],
    'use_global_sanitization' => env('LOG_USE_GLOBAL_SANITIZATION', true),
],
```

---

## Public IDs

Generate user-facing IDs (ULIDs or UUIDs) for models instead of exposing database IDs.

### Configuration

```php
// config/quvel.php
'public_id' => [
    'driver' => env('PUBLIC_ID_DRIVER', 'ulid'), // 'ulid' or 'uuid'
    'column' => env('PUBLIC_ID_COLUMN', 'public_id'),
],
```

### Usage

Add trait to your model:

```php
use Quvel\Core\Concerns\HasPublicId;

class Order extends Model
{
    use HasPublicId;
}
```

Auto-generates public ID on creation:

```php
$order = Order::create([...]);

echo $order->public_id; // '01HQ...' (ULID) or 'uuid-here'

// Find by public ID
$order = Order::wherePublicId('01HQ...')->first();
```

### Route Model Binding

```php
Route::get('/orders/{order:public_id}', function (Order $order) {
    return $order;
});
```

---

## Redirects

Smart redirects that work across web, mobile, and desktop platforms using universal links, custom schemes, or landing pages.

### Configuration

```php
// config/quvel.php
'frontend' => [
    'url' => env('FRONTEND_URL', 'http://localhost:3000'),
    'custom_scheme' => env('FRONTEND_CUSTOM_SCHEME'),

    'redirect_mode' => env('FRONTEND_REDIRECT_MODE', 'universal_links'),
    // Options: 'universal_links', 'custom_scheme', 'landing_page', 'web_only'

    'landing_page_timeout' => env('FRONTEND_LANDING_PAGE_TIMEOUT', 5),

    'allowed_redirect_domains' => explode(',', env('FRONTEND_ALLOWED_DOMAINS', '')),
],
```

### Usage

```php
use Quvel\Core\Facades\Redirect;

// Redirect to frontend path
return Redirect::redirect('/dashboard');

// With query params
return Redirect::redirect('/orders/123', ['status' => 'new']);

// With message
return Redirect::redirectWithMessage('/login', 'Please sign in');

// Get URL without redirecting
$url = Redirect::getUrl('/profile');
```

### Redirect Modes

**Universal Links** (recommended):
Uses HTTPS URLs that open the app if installed, otherwise open in browser.

```env
FRONTEND_REDIRECT_MODE=universal_links
FRONTEND_URL=https://app.example.com
```

**Custom Scheme**:
Uses custom URL scheme (myapp://).

```env
FRONTEND_REDIRECT_MODE=custom_scheme
FRONTEND_CUSTOM_SCHEME=myapp
```

**Landing Page**:
Shows countdown page before redirect.

```env
FRONTEND_REDIRECT_MODE=landing_page
FRONTEND_LANDING_PAGE_TIMEOUT=5
```

**Web Only**:
Always redirects to web URL.

```env
FRONTEND_REDIRECT_MODE=web_only
```

---

## Security

### Internal Request Validation

Protect internal endpoints from external access:

```php
// config/quvel.php
'security' => [
    'internal_requests' => [
        'trusted_ips' => explode(',', env('SECURITY_TRUSTED_IPS', '127.0.0.1,::1')),
        'api_key' => env('SECURITY_API_KEY'),
        'disable_ip_check' => env('SECURITY_DISABLE_IP_CHECK', false),
        'disable_key_check' => env('SECURITY_DISABLE_KEY_CHECK', false),
    ],
],
```

**Protect routes:**
```php
Route::middleware('internal-only')->group(function () {
    Route::get('/internal/stats', [StatsController::class, 'index']);
});
```

**Frontend SSR integration:**
```js
// In your SSR server
fetch('http://api.internal/endpoint', {
    headers: {
        'X-SSR-Key': process.env.SECURITY_API_KEY
    }
});
```

---

## Middleware

### Available Middleware

```php
'captcha'             // Verify captcha token
'config-gate'         // Gate access based on config
'device-detection'    // Detect and track devices
'internal-only'       // Restrict to internal requests
'locale'              // Auto-detect and set locale
'platform-detection'  // Detect platform (web/mobile/desktop)
'trace'               // Generate/propagate trace IDs
```

### Global Configuration

```php
// config/quvel.php
'middleware' => [
    'aliases' => [
        'captcha' => \Quvel\Core\Http\Middleware\VerifyCaptcha::class,
        'config-gate' => \Quvel\Core\Http\Middleware\ConfigGate::class,
        'device-detection' => \Quvel\Core\Http\Middleware\DeviceDetection::class,
        'internal-only' => \Quvel\Core\Http\Middleware\InternalOnly::class,
        'locale' => \Quvel\Core\Http\Middleware\LocaleMiddleware::class,
        'platform-detection' => \Quvel\Core\Http\Middleware\PlatformDetection::class,
        'trace' => \Quvel\Core\Http\Middleware\TraceMiddleware::class,
    ],

    'groups' => [
        'web' => [
            'platform-detection',
            'device-detection',
            'locale',
            'trace',
        ],
        'api' => [
            'platform-detection',
            'device-detection',
            'locale',
            'trace',
        ],
    ],
],
```

---

## Extending the Package

### Custom Implementations

All core services use contracts and can be extended or replaced:

```php
use Quvel\Core\Device\Device as BaseManager;

class CustomDeviceManager extends BaseManager
{
    public function registerDevice(array $deviceData): UserDevice
    {
        $device = parent::registerDevice($deviceData);

        // Add custom logic (webhooks, etc.)

        return $device;
    }
}

// Bind in service provider
$this->app->bind(
    \Quvel\Core\Contracts\Device::class,
    \App\Services\CustomDeviceManager::class
);
```

### Available Contracts

- `CaptchaVerifier`
- `CaptchaDriverInterface`
- `DeviceManager`
- `LocaleResolver`
- `PlatformDetector`
- `PublicIdGenerator`
- `PushManager`
- `PushDriver`
- `AppRedirector`
- `TraceIdGenerator`

---

## Events

### Device Events

- `DeviceRegistered` - Device registered
- `DeviceRemoved` - Device deactivated

### Push Notification Events

- `PushNotificationSent` - Notification sent successfully
- `PushNotificationFailed` - Notification failed

### Captcha Events

- `CaptchaVerifySuccess` - Captcha verification succeeded
- `CaptchaVerifyFailed` - Captcha verification failed

### Trace Events

- `PublicTraceAccepted` - External trace ID accepted

---

## Publishing Assets

```bash
# Publish everything
php artisan vendor:publish --provider="Quvel\Core\Providers\CoreServiceProvider"

# Publish specific assets
php artisan vendor:publish --tag=quvel-config
php artisan vendor:publish --tag=quvel-migrations
php artisan vendor:publish --tag=quvel-routes
php artisan vendor:publish --tag=quvel-lang
php artisan vendor:publish --tag=quvel-views
```

---

## License

MIT
