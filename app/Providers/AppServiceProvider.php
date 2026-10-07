<?php

namespace App\Providers;

use App\Models\Curso;
use App\Models\Ficha;
use App\Policies\CursoPolicy;
use App\Policies\FichaPolicy;
use Carbon\CarbonImmutable;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        $this->configureDefaults();
        $this->configurePolicies();
        $this->configurePdfRenderer();
    }

    /**
     * Register the PDF renderer used by the reports.
     *
     * Remote resources and active content stay disabled because the report
     * markup is built from user provided data, so it must never reach out to
     * external servers nor execute embedded code.
     */
    protected function configurePdfRenderer(): void
    {
        $this->app->singleton(Dompdf::class, function (): Dompdf {
            $options = new Options([
                'isRemoteEnabled' => false,
                'isJavascriptEnabled' => false,
                'isPhpEnabled' => false,
                'defaultFont' => 'DejaVu Sans',
                'defaultPaperSize' => 'a4',
            ]);

            return new Dompdf($options);
        });
    }

    /**
     * Register the policies for the course and enrolment models.
     *
     * These abilities are checked against a Team instance, so they cannot be
     * resolved by the model name convention and must be mapped explicitly.
     */
    protected function configurePolicies(): void
    {
        Gate::policy(Curso::class, CursoPolicy::class);
        Gate::policy(Ficha::class, FichaPolicy::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
