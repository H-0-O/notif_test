<?php

namespace App\Http\Controllers;

use App\Enums\NotificationStatusEnum;
use App\Enums\NotificationUsersStatusEnum;
use App\Http\Resources\NotificationListResource;
use App\Http\Resources\NotificationShowResource;
use App\Models\Notification as NotificationModel;
use App\Models\NotificationUser;
use Illuminate\Support\Facades\Response;

class NotificationController extends Controller
{
    public function index()
    {

        $limit = request()->input('limit', 15);
        $page = request()->input('page', 1);

        $data = NotificationModel::query()
            ->withStat()
            ->paginate(perPage: $limit, page: $page)
            ->toResourceCollection(NotificationListResource::class);

        return Response::gen($data);
    }


    public function show(int $id)
    {
        $data =  NotificationModel::withStat()
            ->with(['users', 'notificationUsers'])
            ->findOrFail($id)
            ->toResource(NotificationShowResource::class);

        return Response::gen($data);
    }

    public function retry(int $id)
    {
        $notification = NotificationModel::findOrFail($id);

        if ($notification->status == NotificationStatusEnum::FAILED->value) {
            $notification->status = NotificationStatusEnum::PENDING;
            NotificationUser::where('status', NotificationUsersStatusEnum::FAILED)
                ->where('notification_id', $notification->id)
                ->update([
                    'status' => NotificationUsersStatusEnum::PENDING,
                    'attempt' => 0
                ]);
            $notification->save();
        }

        return Response::noContent();
    }
}
