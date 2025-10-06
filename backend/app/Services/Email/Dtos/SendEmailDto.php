<?php

namespace App\Services\Email\Dtos;

use App\Dtos\NotificationSenderDto;
use App\Models\Notification;

class SendEmailDto extends NotificationSenderDto
{

    public function __construct(
        public Notification $notification,
        public array $userIds
    ) {}
}
