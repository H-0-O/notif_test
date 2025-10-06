<?php

namespace App\Contracts;

use App\Models\Notification;

interface NotificationImmediateSenderInterface  {

    /**
     * Summary of send
     * @param Notification $notifications
     * @param array $users
     * @return void
     */
    public function sendImmediate(Notification $notification, array $userIds);
}


