<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationUser extends Model
{
    protected $fillable = [
        'id',
        'notification_id',
        'user_id',
        'channel',
        'status'
    ];

    protected $guarded = [];
}
