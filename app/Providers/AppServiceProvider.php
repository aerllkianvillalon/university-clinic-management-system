<?php

namespace App\Providers;

use App\Http\Middleware\EnsureRole;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Livewire update requests (/livewire/update) skip route middleware unless it is
        // registered as persistent. This keeps role checks enforced on every action.
        Livewire::addPersistentMiddleware([EnsureRole::class]);
    }
}
