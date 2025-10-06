<?php

namespace App\Facades;

use App\Contracts\NotificationDeliveryInterface;
use App\Contracts\NotificationImmediateSenderInterface;
use App\Contracts\NotificationSenderInterface;
use App\Enums\NotificationStatusEnum;
use App\Models\Notification as NotificationModel;
use App\Models\NotificationUser;
use App\Services\Email\Dtos\SendEmailDto;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class Notification
{

    /**
     * Summary of send
     * @param string $title
     * @param string $body
     * @param array $types
     * @param array $userIds
     * @throws \Exception
     * @return void
     */
    public static function send(string $title, string $body, array $types, array $userIds)
    {

        DB::transaction(function () use ($title, $body, $types, $userIds) {
            $notification = NotificationModel::create([
                'title' => $title,
                'body' => $body
            ]);



            foreach ($types as $type) {
                $service = self::make($type);
                if (!$service instanceof NotificationSenderInterface) {
                    throw new Exception("the $type is not implemented NotificationSenderInterface");
                }
                $service->send(new SendEmailDto($notification, $userIds));
            }
        });
    }

    public static function deliver()
    {
        $notifications = (new NotificationModel())->getPendingNotifications();

        foreach ($notifications as $notification) {
            $notification->delivery();
        }
    }

    public static function sendImmediate(string $title, string $body, array $types, array $userIds)
    {
        $notification = NotificationModel::create([
            'title' => $title,
            'body' => $body
        ]);

        foreach ($types as $type) {
            $service = self::make($type);

            if ($service instanceof NotificationImmediateSenderInterface) {
                $service->sendImmediate($notification, $userIds);
            } else {
                throw new Exception("This [$type] channel doesn't support Immediate sending");
            }
        }
    }

    /**
     * Summary of make
     * @param string $types
     * @return NotificationDeliveryInterface|NotificationSenderInterface|NotificationImmediateSenderInterface
     * @throws Exception
     */
    public static function make(string $type)
    {
        $service = app("notification.$type");

        if (!$service) {
            throw new Exception("Channel [$type] is not registered");
        }


        return $service;
    }
}
