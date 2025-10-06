<?php

namespace App\Services\Email\Dtos;

use App\Dtos\NotificationSenderDto;
use App\Models\Notification;

class SendSMSDto extends NotificationSenderDto
{

    public function __construct(
        public Notification $notification,
        public array $userIds
    ) {}
}
