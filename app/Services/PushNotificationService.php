<?php

namespace App\Services;

use App\Helper\ResponseHelper;
use App\Http\Resources\NotificationResponseResource;
use App\Http\Requests\SendNotificationRequest;
use App\Interface\DeviceTokenInterface;
use App\Jobs\SendNotificationJob;

class PushNotificationService
{
    protected $repo;

    public function __construct(DeviceTokenInterface $repo)
    {
        $this->repo = $repo;
    }

    public function send(SendNotificationRequest $request)
    {
        $title = $request->title;
        $body = $request->body;
        $data = $request->data ?? [];

        if ($request->has('user_ids') && !empty($request->user_ids)) {
            return $this->sendToUsers($request->user_ids, $title, $body, $data, $request);
        } elseif ($request->has('tokens') && !empty($request->tokens)) {
            return $this->sendToTokens($request->tokens, $title, $body, $data, $request);
        }

        return ResponseHelper::error(
            'error',
            'Either user_ids or tokens must be provided',
            400
        );
    }

    protected function sendToUsers(array $userIds, string $title, string $body, array $data, $request)
    {
        $tokens = $this->repo->tokensForUsers($userIds);
        
        if (empty($tokens)) {
            $result = (new NotificationResponseResource([
                'count' => 0,
                'message' => 'No device tokens found for the specified users',
            ]))->toArray($request);
            
            return ResponseHelper::success(
                'success',
                'No device tokens found for the specified users',
                $result
            );
        }
        
        // Dispatch job to queue
        SendNotificationJob::dispatch($title, $body, $data, $userIds, null);
        
        $count = count($tokens);
        $result = (new NotificationResponseResource([
            'count' => $count,
            'message' => "Notification queued for {$count} device(s)",
        ]))->toArray($request);
        
        return ResponseHelper::success(
            'success',
            "Notification queued for {$count} device(s)",
            $result
        );
    }

    protected function sendToTokens(array $tokens, string $title, string $body, array $data, $request)
    {
        if (empty($tokens)) {
            $result = (new NotificationResponseResource([
                'count' => 0,
                'message' => 'No device tokens provided',
            ]))->toArray($request);
            
            return ResponseHelper::success(
                'success',
                'No device tokens provided',
                $result
            );
        }
        
        // Dispatch job to queue
        SendNotificationJob::dispatch($title, $body, $data, null, $tokens);
        
        $count = count($tokens);
        $result = (new NotificationResponseResource([
            'count' => $count,
            'message' => "Notification queued for {$count} device(s)",
        ]))->toArray($request);
        
        return ResponseHelper::success(
            'success',
            "Notification queued for {$count} device(s)",
            $result
        );
    }
}
