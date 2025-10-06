<?php

namespace App\Models;

use App\Enums\NotificationStatusEnum;
use App\Enums\NotificationUsersStatusEnum;
use App\Facades\Notification as FacadesNotification;
use App\Services\Email\Dtos\DeliverEmailDto;
use App\Services\Email\Dtos\UserEmailDto;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Notification extends Model
{
    protected $fillable = [
        'id',
        'title',
        'body',
        'status',
    ];

    protected $guarded = [];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, NotificationUser::class, 'notification_id', 'user_id');
    }

    public function notificationUsers(): HasMany
    {
        return $this->hasMany(NotificationUser::class);
    }


    public function delivery(): void
    {
        $pending = $this->notificationUsers()
            ->with('user')
            ->where('status', NotificationUsersStatusEnum::PENDING->value)
            ->orWhere(function ($q) {
                $q->where('status', NotificationUsersStatusEnum::PROCESSING->value)
                    ->where('expire_at', '>', now());
            })
            ->orWhere(function ($q) {
                $q->where('notification_users.status', NotificationUsersStatusEnum::FAILED->value)
                    ->where('notification_users.attempt', '<', 3);
            })
            ->limit(500)
            ->orderBy('id')
            ->get();

        if ($pending->isEmpty()) {
            $this->refreshStatus();
            return;
        }

        $this->update([
            'status' => NotificationStatusEnum::PROCESSING
        ]);

        $pending = $pending->groupBy('channel');

        foreach ($pending as $channel => $notificationUsers) {
            $service = FacadesNotification::make($channel);
            $transformedCollection = $notificationUsers->map(function (NotificationUser $notificationUser) {
                return new UserEmailDto($notificationUser->user->email, $notificationUser);
            });
            $deliverEmailDto = new DeliverEmailDto($this, $transformedCollection);
            $service->deliver($deliverEmailDto);
        }
        $this->refreshStatus();
    }

    public function refreshStatus()
    {
        $stats = NotificationUser::query()
            ->where('notification_id', $this->id)
            ->selectRaw(
                '
                COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = ? AND attempt >= 3 THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as processing
            ',
                [
                    NotificationStatusEnum::COMPLETED->value,
                    NotificationStatusEnum::FAILED->value,
                    NotificationStatusEnum::PENDING->value,
                    NotificationStatusEnum::PROCESSING->value,
                ]
            )
            ->first();



        $status = NotificationStatusEnum::PENDING;
        if ($stats->total == $stats->sent) {
            $status = NotificationStatusEnum::COMPLETED;
        } else if ($stats->total == $stats->failed) {
            $status = NotificationStatusEnum::FAILED;
        } else if ($stats->sent > 0 && ($stats->sent + $stats->failed) <= $stats->total) {
            $status = NotificationStatusEnum::PARTIAL;
        } else if ($stats->processing > 0 || $stats->pending > 0) {
            $status = NotificationStatusEnum::PROCESSING;
        }

        $this->update(['status' => $status]);
    }

    public function getPendingNotifications()
    {
        return $this::query()->whereIn('status', [
            NotificationStatusEnum::PENDING->value,
            NotificationStatusEnum::PARTIAL->value,
            NotificationStatusEnum::PROCESSING->value
        ])
            ->limit(10)
            ->orderBy('id')
            ->get();
    }

    public function scopeWithStat(Builder $query)
    {
        $sub = DB::table('notification_users')
            ->selectRaw(
                " notification_id,
            COUNT(*) as total,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as sent,
            SUM(CASE WHEN status = ? AND attempt >= 3 THEN 1 ELSE 0 END) as failed,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as processing",
                [
                    NotificationStatusEnum::COMPLETED->value,
                    NotificationStatusEnum::FAILED->value,
                    NotificationStatusEnum::PENDING->value,
                    NotificationStatusEnum::PROCESSING->value,
                ]
            )
            ->groupBy('notification_id');

        return $query
            ->leftJoinSub($sub, 'notification_user_stats', function ($join) {
                $join->on('notifications.id', '=', 'notification_user_stats.notification_id');
            })
            ->addSelect('notifications.*')
            ->addSelect([
                'notification_user_stats.total',
                'notification_user_stats.sent',
                'notification_user_stats.failed',
                'notification_user_stats.pending',
                'notification_user_stats.processing',
            ]);
    }
}
