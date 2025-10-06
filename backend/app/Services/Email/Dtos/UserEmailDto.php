<?php

namespace App\Services\Email\Dtos;

use App\Models\Notification;
use App\Models\NotificationUser;

class UserEmailDto
{
    public function __construct(
        public string $email,
        public NotificationUser $notificationUser
    ) {}
}
