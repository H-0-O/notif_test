<?php

namespace App\Contracts;

use App\Enums\NotificationStatusEnum;
use App\Models\User;

interface NotificationChannelInterface  {
    public function send(User $user , string $title , string $body , NotificationStatusEnum $status = NotificationStatusEnum::PENDING);
}