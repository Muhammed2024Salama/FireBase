<?php

namespace App\Interface;

interface DeviceTokenInterface
{
    public function create(array $data);
    public function findByToken(string $token);
    public function deleteByToken(string $token);
    public function tokensForUsers(array $userIds);
}
