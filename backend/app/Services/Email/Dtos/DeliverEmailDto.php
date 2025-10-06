<?php

namespace App\Services\Email\Dtos;

use App\Dtos\NotificationDeliveryDto;
use App\Dtos\NotificationSenderDto;
use App\Models\Notification;
use Illuminate\Support\Collection;

class DeliverEmailDto extends NotificationDeliveryDto
{

    /**
     * Summary of __construct
     * @param \App\Models\Notification 
     * @param Collection<UserEmailDto> $users
     */
    public function __construct(
        public Notification $notification,
        public mixed $users
    ) {}
}
