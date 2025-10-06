<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'users' => UserListResource::collection($this->users),
            'notification_users' => NotificationUserResource::collection($this->notificationUsers),
            'stats' => [
                'total' => $this->total,
                'sent' => $this->sent,
                'failed' => $this->failed,
                'pending' => $this->pending,
                'processing' => $this->processing
            ]
        ];
    }
}
