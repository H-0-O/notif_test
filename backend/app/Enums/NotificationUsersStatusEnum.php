<?php
 
namespace App\Enums;

enum NotificationUsersStatusEnum: string {
    case PENDING = "pending";

    case PROCESSING = "processing";

    case COMPLETED = "completed";

    case FAILED = "failed";


}