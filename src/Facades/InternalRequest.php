<?php

declare(strict_types=1);

namespace Quvel\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Http\Request;
use Quvel\Core\Contracts\InternalRequestValidator;

/**
 * Internal request validation facade.
 *
 * @method static bool isInternalRequest(Request $request)
 * @method static bool isValidIp(Request $request)
 * @method static bool isValidApiKey(Request $request)
 */
class InternalRequest extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return InternalRequestValidator::class;
    }
}