<?php

namespace Tests\Unit;

use App\Enums\NotificationStatusEnum;
use App\Facades\Notification;
use App\Models\Notification as NotificationModel;
use App\Models\NotificationUser;
use App\Models\User;
use App\Services\Email\Dtos\SendEmailDto;
use App\Services\Email\EmailNotificationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Mockery\MockInterface;
use Tests\TestCase;

class EmailNotificationServiceTest extends TestCase
{

    use RefreshDatabase;

    private Collection $users;

    private EmailNotificationService $emailNotificationService;

    public function setUp(): void
    {
        parent::setUp();

        $this->users = User::factory()->count(10)->create();
        $this->emailNotificationService = $this->app->make(EmailNotificationService::class);
    }


    public function test_sending()
    {
        $title = "Test Email";
        $body = "This is just testing email app";
        $ids = $this->users->pluck('id')->toArray();

        $notification = NotificationModel::create([
            'title' => $title,
            'body' => $body
        ]);

        $this->assertDatabaseCount(NotificationModel::class, 1);

        $this->assertDatabaseHas(NotificationModel::class, [
            'title' => $title,
            'body' => $body
        ]);



        $notification = NotificationModel::first();

        $dto = new SendEmailDto($notification, $ids);

        $this->emailNotificationService->send($dto);

        foreach ($this->users as $user) {
            $this->assertDatabaseHas(NotificationUser::class, [
                'user_id' => $user->id,
                'notification_id' => $notification->id,
                'channel' => 'email',
                'status' => NotificationStatusEnum::PENDING,
                'expire_at' => null
            ]);
        }
    }

    public function test_delivery()
    {
        Mail::fake();

        $title = "Test Email";
        $body = "This is just testing email app";
        $ids = $this->users->pluck('id')->toArray();

        $notification = NotificationModel::create([
            'title' => $title,
            'body' => $body
        ]);

        $dto = new SendEmailDto($notification, $ids);

        $this->emailNotificationService->send($dto);

        $notifications = (new NotificationModel())->getPendingNotifications();

        foreach ($notifications as $notification) {
            $notification->delivery();
        }

        foreach ($this->users as $user) {
            $this->assertDatabaseHas(NotificationUser::class, [
                'user_id' => $user->id,
                'notification_id' => $notification->id,
                'channel' => 'email',
                'status' => NotificationStatusEnum::COMPLETED,
                'expire_at' => null
            ]);
        }
    }
}
