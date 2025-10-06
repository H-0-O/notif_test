<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->bindNotificationChannels();

        // this macro generate all responses
        Response::macro('gen', function (mixed $data, $meta = [], $errors = null, $code = 200) {
            if ($errors != null) {
                $errors = is_array($errors) && array_is_list($errors) ? $errors : [$errors]; // means if it's not key value array and it's an array of object 
            }

            if ($data instanceof AnonymousResourceCollection) {
                $pagination = $data->resource->toArray(); // resource is the paginator
                return Response::json([
                    'data' => $data->collection,
                    'meta' => [
                        ...$meta,
                        'current_page' => $pagination['current_page'] ?? null,
                        'last_page' => $pagination['last_page'] ?? null,
                        'per_page' => $pagination['per_page'] ?? null,
                        'total' => $pagination['total'] ?? null,
                    ],
                    'errors' => $errors,
                ], $code);
            }
            return Response::json([
                'data' => $data,
                'meta' => $meta,
                'errors' => $errors
            ], $code);
        });

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    private function bindNotificationChannels(){

        $channels = config('notifications.channels');

        foreach($channels as $key => $service) {
            $this->app->bind("notification.$key" , $service);
        }
    }
}
