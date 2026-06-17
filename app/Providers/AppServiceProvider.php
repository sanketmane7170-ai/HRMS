<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Events\OnlineStatusChanged;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Author: Sanket - Force HTTPS in all environments except local
        if (!app()->environment('local')) {
            URL::forceScheme('https');
        }

        Paginator::useBootstrap();

        if (Schema::hasTable('settings')) {
            $setting = [
                'app.name' => getSetting('site_title'),
                //  'app.debug' =>  getSetting('site_debug_mode') == 'true' ? true : false,
                // 'app.timezone' => getSetting('site_timezone'),
            ];
            config($setting);
        }
        // Handle online status
        Auth::viaRequest('custom-token', function ($request) {
            $user = Auth::user();
            if ($user) {
                $user->update(['online' => true]);
                try {
                    broadcast(new OnlineStatusChanged($user))->toOthers();
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }
            return $user;
        });

        // Handle offline status on logout
        Event::listen('Illuminate\Auth\Events\Logout', function ($event) {
            $user = $event->user;
            if ($user) {
                $user->update(['online' => false]);
                try {
                    broadcast(new OnlineStatusChanged($user))->toOthers();
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }
        });
        try {

        
            Event::listen('eloquent.creating: *', function ($event, $models) {
                // Log::info('🟢 [ELOQUENT:CREATING] Triggered', [
                //     'event' => $event,
                //     'models' => collect($models)->map(fn($m) => get_class($m))->toArray(),
                // ]);

                $model = $models[0] ?? null;
                if ($model instanceof \Illuminate\Database\Eloquent\Model && Auth::check()) {
                    $userId = Auth::id();
                    // Log::info('Creating model', [
                    //     'model' => get_class($model),
                    //     'table' => $model->getTable(),
                    //     'user_id' => $userId,
                    // ]);

                    if (Schema::hasColumn($model->getTable(), 'created_by')) {
                        $model->created_by = $userId;
                    }
                    if (Schema::hasColumn($model->getTable(), 'updated_by')) {
                        $model->updated_by = $userId;
                    }
                }
            });

            Event::listen('eloquent.updating: *', function ($event, $models) {
                // Log::info('🟡 [ELOQUENT:UPDATING] Triggered', [
                //     'event' => $event,
                //     'models' => collect($models)->map(fn($m) => get_class($m))->toArray(),
                // ]);

                $model = $models[0] ?? null;
                if ($model instanceof \Illuminate\Database\Eloquent\Model && Auth::check()) {
                    $userId = Auth::id();
                    $original = $model->getOriginal();
                    $changes = $model->getDirty();

                    // Log::info('Updating model', [
                    //     'model' => get_class($model),
                    //     'table' => $model->getTable(),
                    //     'user_id' => $userId,
                    //     'original_values' => $original,
                    //     'changed_values' => $changes,
                    // ]);

                    if (Schema::hasColumn($model->getTable(), 'updated_by')) {
                        $model->updated_by = $userId;
                    }
                }
            });

            Event::listen('eloquent.saving: *', function ($event, $models) {
                // Log::info('🔵 [ELOQUENT:SAVING] Triggered', [
                //     'event' => $event,
                //     'models' => collect($models)->map(fn($m) => get_class($m))->toArray(),
                // ]);

                $model = $models[0] ?? null;
                if ($model instanceof \Illuminate\Database\Eloquent\Model && Auth::check()) {
                    $userId = Auth::id();

                    // Log::info('Saving model', [
                    //     'model' => get_class($model),
                    //     'table' => $model->getTable(),
                    //     'user_id' => $userId,
                    //     'exists' => $model->exists,
                    // ]);

                    if (Schema::hasColumn($model->getTable(), 'created_by') && !$model->exists) {
                        $model->created_by = $userId;
                    }
                    if (Schema::hasColumn($model->getTable(), 'updated_by')) {
                        $model->updated_by = $userId;
                    }
                }
            });
        } catch (\Throwable $e) {
            Log::warning('Could not register GlobalModelObserver: ' . $e->getMessage());
        }
    }
}
