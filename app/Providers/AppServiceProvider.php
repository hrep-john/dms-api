<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use OwenIt\Auditing\Models\Audit;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(\App\Http\Services\Contracts\EloquentServiceInterface::class, \App\Http\Services\BaseService::class);
        $this->app->bind(\App\Http\Services\Contracts\UserServiceInterface::class, \App\Http\Services\UserService::class);
        $this->app->bind(\App\Http\Services\Contracts\DocumentServiceInterface::class, \App\Http\Services\DocumentService::class);
        $this->app->bind(\App\Http\Services\Contracts\DocumentDetailMetadataServiceInterface::class, \App\Http\Services\DocumentDetailMetadataService::class);
        $this->app->bind(\App\Http\Services\Contracts\DocumentEntityMetadataServiceInterface::class, \App\Http\Services\DocumentEntityMetadataService::class);
        $this->app->bind(\App\Http\Services\Contracts\UserDefinedFieldServiceInterface::class, \App\Http\Services\UserDefinedFieldService::class);
        $this->app->bind(\App\Http\Services\Contracts\TenantServiceInterface::class, \App\Http\Services\TenantService::class);
        $this->app->bind(\App\Http\Services\Contracts\TenantSettingServiceInterface::class, \App\Http\Services\TenantSettingService::class);
        $this->app->bind(\App\Http\Services\Contracts\RoleServiceInterface::class, \App\Http\Services\RoleService::class);
        $this->app->bind(\App\Http\Services\Contracts\ReportBuilderServiceInterface::class, \App\Http\Services\ReportBuilderService::class);
        $this->app->bind(\App\Http\Services\Contracts\CustomReportServiceInterface::class, \App\Http\Services\CustomReportService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Fix for MySQL < 5.7.7 and MariaDB < 10.2.2
        Schema::defaultStringLength(191); // Update defaultStringLength

        Audit::creating(function (Audit $model) {
            if ($model->event === 'updated' && $model->old_values === $model->new_values) {
                return false;
            }
        });
    }
}
