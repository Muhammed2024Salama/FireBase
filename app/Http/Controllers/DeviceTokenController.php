<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyDeviceTokenRequest;
use App\Http\Requests\SendNotificationRequest;
use App\Http\Requests\StoreDeviceTokenRequest;
use App\Services\DeviceTokenService;
use App\Services\PushNotificationService;

class DeviceTokenController extends Controller
{
    public function store(StoreDeviceTokenRequest $request, DeviceTokenService $deviceTokenService)
    {
        return $deviceTokenService->store($request);
    }

    public function destroy(DestroyDeviceTokenRequest $request, DeviceTokenService $deviceTokenService)
    {
        return $deviceTokenService->delete($request);
    }

    public function sendNotification(SendNotificationRequest $request, PushNotificationService $pushService)
    {
        return $pushService->send($request);
    }
}
