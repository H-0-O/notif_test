<?php

namespace App\Models;

use App\Enums\NotificationUsersStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationUser extends Model
{
    protected $fillable = [
        'id',
        'notification_id',
        'user_id',
        'channel',
        'status',
        'expire_at',
        'attempt',
        'error_message'
    ];

    protected $guarded = [];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }


    public static function pendingNotifications($limit = 100)
    {
        return NotificationUser::query()
            ->select('notification_users.*')
            ->with(['user', 'notification'])
            ->where('status', NotificationUsersStatusEnum::PENDING->value)
            ->orWhere(function ($q) {
                $q->where('status', NotificationUsersStatusEnum::PROCESSING->value)
                    ->where('expire_at', '>', now());
            })
            ->orWhere(function ($q) {
                $q->where('status', NotificationUsersStatusEnum::FAILED->value)
                    ->where('attempt', '<', 3);
            })
            ->orderBy('channel')
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }
}
