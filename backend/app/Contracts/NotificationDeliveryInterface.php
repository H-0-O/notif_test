<?php

namespace App\Contracts;

use App\Dtos\NotificationDeliveryDto;
use App\Enums\NotificationStatusEnum;
use App\Models\Notification;
use App\Models\NotificationUser;
use App\Models\User;
use Illuminate\Support\Collection;
use Iterator;

interface NotificationDeliveryInterface  {

    /**
     * Summary of send
     * @param NotificationUser[] $notificationUsers
     * @param array $users
     * @return void
     */
    public function deliver(NotificationDeliveryDto $dto);
}

