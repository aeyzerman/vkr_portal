<?php

namespace App\Providers;

use App\Enums\ThesisStatus;
use App\Models\Thesis;
use App\Models\Topic;
use App\Models\User;
use App\Policies\ThesisPolicy;
use App\Policies\TopicPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::define('admin', fn(User $user) => $user->isAdmin());

        Gate::define('supervisor', fn(User $user) => $user->isSupervisor());

        Gate::define('commission', fn(User $user) => $user->isCommission() || $user->isReviewer());

        // Кто может менять статус работы
        Gate::define('change-thesis-status', function (User $user, Thesis $thesis) {
            // Руководитель этой работы
            if ($user->isSupervisor() && $thesis->supervisor_id === $user->id) {
                return true;
            }
            // Комиссия и рецензенты видят все работы в статусах review/approved
            if (($user->isCommission() || $user->isReviewer())
                && in_array($thesis->status?->value, [ThesisStatus::Review->value, ThesisStatus::Approved->value], true)) {
                return true;
            }
            // Админ может всё
            return $user->isAdmin();
        });

        $this->registerPolicies();
    }

    private function registerPolicies(): void
    {
        Gate::policy(Topic::class, TopicPolicy::class);
        Gate::policy(Thesis::class, ThesisPolicy::class);
    }
}
