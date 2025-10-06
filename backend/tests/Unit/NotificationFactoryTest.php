<?php

namespace Tests\Unit;

use App\Enums\NotificationUsersStatusEnum;
use App\Facades\Notification;
use App\Models\Notification as NotificationModel;
use App\Models\NotificationUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationFactoryTest extends TestCase {

    use RefreshDatabase;

    private Collection $users; 

    public function setUp(): void {
        parent::setUp();

        $this->users = User::factory()->count(10)->create();
    }


    public function test_sending(){
        $title = "Test Email";
        $body = "This is just testing email app";
        $ids = $this->users->pluck('id')->toArray();

        Notification::send( $title,  $body, ['email'] , $ids );

        $this->assertDatabaseCount(NotificationModel::class , 1);

        $this->assertDatabaseHas(NotificationModel::class , [
            'title' => $title,
            'body' => $body
        ]);

        $notification = NotificationModel::first();

        foreach($this->users as $user) {
            $this->assertDatabaseHas(NotificationUser::class, [
                'user_id' => $user->id,
                'notification_id' => $notification->id,
                'channel' => 'email',
                'status' => NotificationUsersStatusEnum::PENDING,
                'expire_at' => null
            ]);
        }
    }

    public function test_delivery(){
        $title = "Test Email";
        $body = "This is just testing email app";
        $ids = $this->users->pluck('id')->toArray();

        Notification::send( $title,  $body, ['email'] , $ids );

        Notification::deliver();

        $notification = NotificationModel::first();
        
        foreach($this->users as $user) {
            $this->assertDatabaseHas(NotificationUser::class, [
                'user_id' => $user->id,
                'notification_id' => $notification->id,
                'channel' => 'email',
                'status' => NotificationUsersStatusEnum::COMPLETED,
                'expire_at' => null
            ]);
        }
    }

}