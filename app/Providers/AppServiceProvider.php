<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use App\Models\Category;

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
        // $user->role is a belongsTo relationship returning a Role model.
        // The 'role_name' column values match exactly what's in DB (lowercase_underscores).
        Gate::define('admin', function ($user) {
            return $user->role?->role_name === 'admin';
        });

        Gate::define('head_of_finance', function ($user) {
            return $user->role?->role_name === 'head_of_finance';
        });

        Gate::define('accountant', function ($user) {
            return $user->role?->role_name === 'accountant';
        });

        Gate::define('deputy_head_of_faculty', function ($user) {
            return $user->role?->role_name === 'deputy_head_of_faculty';
        });

        Gate::define('head_of_faculty', function ($user) {
            return $user->role?->role_name === 'head_of_faculty';
        });

        // Provide categories to header component for category filtering links
        // View::composer('components.header', function ($view) {
        //     $view->with('categories', Category::all());
        // });

        // // Provide categories to filter component
        // View::composer('components.filter', function ($view) {
        //     $view->with('categories', Category::select(['name','cate_id'])->orderBy('name')->get());
        // });
    }
}
