<?php

namespace App\Dtos;

use App\Models\Notification;

abstract class NotificationSenderDto
{

    public function __construct(
        public Notification $notification
    ) {}
}
