<?php

namespace App\Jobs;

use App\Interface\DeviceTokenInterface;
use App\Notifications\FcmNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Notification;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public $userIds;
    public $tokens;
    public $title;
    public $body;
    public $data;

    public function __construct(
        string $title,
        string $body,
        array $data = [],
        ?array $userIds = null,
        ?array $tokens = null
    ) {
        $this->userIds = $userIds;
        $this->tokens = $tokens;
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
    }

    public function handle(DeviceTokenInterface $repo)
    {
        $tokens = $this->tokens;

        if ($this->userIds && !empty($this->userIds)) {
            $tokens = $repo->tokensForUsers($this->userIds);
        }

        if (empty($tokens)) {
            return;
        }

        Notification::route('fcm', $tokens)
            ->notify(new FcmNotification($this->title, $this->body, $this->data));
    }
}
