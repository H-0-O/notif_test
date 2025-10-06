<?php

namespace App\Dtos;

use App\Models\Notification;

abstract class NotificationDeliveryDto {
    
    public function __construct(
        public Notification $notification
    ) {}
}