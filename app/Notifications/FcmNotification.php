<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotificationResource;

class FcmNotification extends Notification
{
    public string $title;
    public string $body;
    public array $data;

    public function __construct(
        string $title,
        string $body,
        array $data = []
    )
    {
        $this->title = $title;
        $this->body  = $body;
        $this->data  = $data;
    }

    public function via($notifiable)
    {
        return ['fcm'];
    }

    public function toFcm($notifiable)
    {
        return FcmMessage::create()
            ->notification(
                FcmNotificationResource::create($this->title, $this->body)
            )
            ->data($this->data);
    }
}
