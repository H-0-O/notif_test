<?php

namespace App\Contracts;

use App\Dtos\NotificationSenderDto;
use App\Models\Notification;

interface NotificationSenderInterface  {

    /**
     * Summary of send
     * @param Notification $notifications
     * @param array $users
     * @return void
     */
    public function send(NotificationSenderDto $notificationSenderDto);
}


