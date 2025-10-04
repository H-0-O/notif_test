<?php 

namespace App\Services;

use App\Contracts\NotificationChannelInterface;
use App\Enums\NotificationStatusEnum;
use App\Models\User;

class EmailNotificationService implements NotificationChannelInterface {
    public function send(User $user, string $title, string $body, NotificationStatusEnum $status = NotificationStatusEnum::PENDING){

    }
} 