<?php

use App\Services\Email\EmailNotificationService;
use App\Services\SMS\SMSNotificationService;

return [
    'channels' => [
        'email' => EmailNotificationService::class,
        'sms' => SMSNotificationService::class,
    ]
];