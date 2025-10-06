<?php

namespace App\Services\Email;

use App\Contracts\NotificationDeliveryInterface;
use App\Contracts\NotificationImmediateSenderInterface;
use App\Contracts\NotificationSenderInterface;
use App\Dtos\NotificationDeliveryDto;
use App\Dtos\NotificationSenderDto;
use App\Enums\NotificationUsersStatusEnum;
use App\Models\Notification;
use App\Services\Email\Dtos\DeliverEmailDto;
use App\Services\Email\Dtos\SendEmailDto;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EmailNotificationService implements
    NotificationSenderInterface,
    NotificationDeliveryInterface,
    NotificationImmediateSenderInterface
{
    public function send(NotificationSenderDto|SendEmailDto $dto)
    {
        $dto->notification->users()->attach($dto->userIds, [
            'channel' => 'email',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }


    public function deliver(NotificationDeliveryDto|DeliverEmailDto $dto)
    {
        foreach ($dto->users as $user) {
                try {
                    // currently we don't support html / pls just send plain text for now (:
                    Mail::send([], [], function (Message $message) use ($user , $dto) {
                        $message->to($user->email)
                            ->subject($dto->notification->title)
                            ->text($dto->notification->body);
                    });

                    $user->notificationUser->update([
                        'status' => NotificationUsersStatusEnum::COMPLETED,
                        'error_message' => null
                    ]);
                } catch (Throwable $e) {
                    $user->notificationUser->update([
                        'status' => NotificationUsersStatusEnum::FAILED,
                        'attempt' => DB::raw('attempt + 1'),
                        'error_message' => $e->getMessage()
                    ]);
                }
        }
    }

    public function sendImmediate(Notification $notification, array $userIds)
    {
        // $this->send($notification, $userIds);

        // $notificationUsers = $notification->users()
        //     ->wherePivot('channel', 'email')
        //     ->get();

        // $this->deliver($notificationUsers);
    }
}
