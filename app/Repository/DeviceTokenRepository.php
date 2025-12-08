<?php

namespace App\Repository;

use App\Interface\DeviceTokenInterface;
use App\Models\DeviceToken;

class DeviceTokenRepository implements DeviceTokenInterface
{
    public function create(array $data)
    {
        return DeviceToken::updateOrCreate(['token' => $data['token']], $data);
    }

    public function findByToken(string $token)
    {
        return DeviceToken::where('token', $token)->first();
    }

    public function deleteByToken(string $token)
    {
        return DeviceToken::where('token', $token)->delete();
    }

    public function tokensForUsers(array $userIds)
    {
        return DeviceToken::whereIn('user_id', $userIds)->pluck('token')->toArray();
    }
}
