<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResponseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = is_array($this->resource) ? $this->resource : $this->resource->toArray();
        
        return [
            'count' => $data['count'] ?? 0,
            'message' => $data['message'] ?? 'Notification sent successfully',
            'sent_at' => now()->toDateTimeString(),
        ];
    }
}

