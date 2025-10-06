<?php

namespace Tests\Feature;

use App\Enums\NotificationUsersStatusEnum;
use App\Models\User;
use App\Models\Notification as NotificationModel;
use App\Models\NotificationUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotifyUserTest extends TestCase
{
    use RefreshDatabase;

    private array $userIds;

    public function setUp(): void
    {
        parent::setUp();

        $this->userIds = User::factory()->count(5)->create()->pluck('id')->toArray();
    }

    public function it_sends_notification_to_users()
    {
        Mail::fake(); 

        $payload = [
            'title' => 'Test Notification',
            'body' => 'This is a test notification',
            'types' => ['email'],
            'userIds' => $this->userIds,
        ];

        $response = $this->postJson('/api/send', $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('notifications', [
            'title' => $payload['title'],
            'body' => $payload['body'],
        ]);

        $notification = NotificationModel::where('title', $payload['title'])->first();

        foreach ($this->userIds as $id) {
            $this->assertDatabaseHas('notification_users', [
                'user_id' => $id,
                'notification_id' => $notification->id,
                'channel' => 'email',
                'status' => NotificationUsersStatusEnum::PENDING,
            ]);
        }

        foreach ($this->userIds as $id) {
            $user = User::find($id);
            Mail::assertSent(function ($mail) use ($user, $payload) {
                return $mail->hasTo($user->email)
                    && $mail->subject === $payload['title'];
            });
        }
    }

    public function it_throws_exception_for_missing_users()
    {
        $invalidUserIds = array_merge($this->userIds, [999, 1000]); 

        $payload = [
            'title' => 'Test Notification',
            'body' => 'This is a test notification',
            'types' => ['email'],
            'userIds' => $invalidUserIds,
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('these users not registered 999 , 1000');

        $this->postJson('/api/send', $payload);
    }

    public function it_returns_validation_errors_for_invalid_payload()
    {
        $payload = [
            'title' => '',
            'body' => '',
            'types' => ['slack'], 
            'userIds' => ['aaa'], 
        ];

        $response = $this->postJson('/api/send', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'title',
            'body',
            'types.0',
            'userIds.0'
        ]);
    }
}
