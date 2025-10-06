<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationListResource extends JsonResource
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
