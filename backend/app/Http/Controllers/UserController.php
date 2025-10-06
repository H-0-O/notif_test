<?php

namespace App\Http\Controllers;

use App\Facades\Notification;
use App\Http\Requests\SendNotificationRequest;
use App\Models\User;
use Illuminate\Support\Facades\Response;

class UserController extends Controller
{
    public function notifyUser(SendNotificationRequest $request)
    {
        $data = $request->validated();

        $users = User::query()->select('id')
            ->whereIn('id', $data['userIds'])
            ->pluck('id');

        $missing = collect($data['userIds'])->diff($users);
        if ($missing->isNotEmpty()) {
            throw new \Exception("these users not registered " . $missing->implode(' , '));
        }

      
        Notification::send($data['title'] , $data['body'] , $data['types'] , $users->toArray());
        return Response::noContent();
    }

    public function deliver(){
        Notification::deliver();
        return Response::noContent();
    }
}
