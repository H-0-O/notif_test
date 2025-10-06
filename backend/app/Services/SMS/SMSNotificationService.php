<?php

namespace App\Services\SMS;

use App\Contracts\NotificationSenderInterface;
use App\Dtos\NotificationSenderDto;
use App\Models\Notification;
use App\Models\User;
use App\Services\Email\Dtos\SendSMSDto;
use Exception;
use Illuminate\Support\Collection;

class SMSNotificationService implements NotificationSenderInterface
{
    public function send(NotificationSenderDto|SendSMSDto $dto)
    {
        $usersWithoutPhone = User::query()
            ->select('id')
            ->whereNull('phone')
            ->whereIn('id', $dto->userIds)
            ->pluck('id');

        if ($usersWithoutPhone->isNotEmpty()) {
            throw new Exception("There users don't registered their phone");
        }

        $dto->notification->users()->attach($dto->userIds, [
            'channel' => 'sms',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }


    public function deliver(array|Collection $notificationUsers){
        throw new Exception("SMS delivery No implemented");
    }
}
