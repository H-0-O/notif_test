<?php
 
namespace App\Enums;

enum NotificationStatusEnum: string {
    case PENDING = "pending";

    case PROCESSING = "processing";

    case COMPLETED = "compiled";

    case FAILED = "failed";

}