<?php

namespace App\Services;

use App\Helper\ResponseHelper;
use App\Http\Resources\DeviceTokenResource;
use App\Http\Requests\StoreDeviceTokenRequest;
use App\Http\Requests\DestroyDeviceTokenRequest;
use App\Interface\DeviceTokenInterface;
use Illuminate\Support\Facades\Auth;

class DeviceTokenService
{
    protected $repo;

    public function __construct(DeviceTokenInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreDeviceTokenRequest $request)
    {
        $data = [
            'token' => $request->token,
            'platform' => $request->platform,
            'user_id' => Auth::id(),
        ];
        
        $deviceToken = $this->repo->create($data);
        
        $result = (new DeviceTokenResource($deviceToken))->toArray($request);

        return ResponseHelper::success(
            'success',
            'Device token stored successfully',
            $result,
            201
        );
    }

    public function delete(DestroyDeviceTokenRequest $request)
    {
        $deleted = $this->repo->deleteByToken($request->token);
        
        if ($deleted) {
            return ResponseHelper::success(
                'success',
                'Device token deleted successfully'
            );
        }
        
        return ResponseHelper::error(
            'error',
            'Device token not found',
            404
        );
    }
}

