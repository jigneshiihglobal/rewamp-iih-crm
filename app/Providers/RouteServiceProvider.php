<?php

namespace App\Providers;

use App\Enums\InvoiceType;
use App\Helpers\EncryptionHelper;
use App\Models\Bank;
use App\Models\Client;
use App\Models\EmailSignature;
use App\Models\Expense;
use App\Models\ExpenseNote;
use App\Models\File;
use App\Models\FollowUp;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\LeadNote;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });

        Route::bind('lead', function ($encryptedId) {
            return Lead::where('id', EncryptionHelper::decrypt($encryptedId))
                ->when(Auth::user()->hasRole(['Admin', 'Superadmin','Marketing']), function ($query) {
                    return $query->withTrashed();
                })
                ->firstOrFail();
        });
        Route::bind('leadNote', function ($encryptedId) {
            return LeadNote::where('id', EncryptionHelper::decrypt($encryptedId))->firstOrFail();
        });

        Route::bind('user', function ($encryptedId) {
            return User::where('id', EncryptionHelper::decrypt($encryptedId))
                ->when(Auth::user()->hasRole(['Admin', 'Superadmin']), function ($query) {
                    $query->withTrashed();
                })
                ->firstOrFail();
        });

        Route::bind('attachment', function ($encryptedId) {
            return File::where('id', EncryptionHelper::decrypt($encryptedId))->firstOrFail();
        });

        Route::bind('file', function ($encryptedId) {
            return File::where('id', EncryptionHelper::decrypt($encryptedId))->firstOrFail();
        });

        Route::bind('lead_status', function ($encryptedId) {
            return LeadStatus::where('id', EncryptionHelper::decrypt($encryptedId))
                ->when(Auth::user()->hasRole(['Admin', 'Superadmin']), function ($query) {
                    $query->withTrashed();
                })
                ->firstOrFail();
        });

        Route::bind('lead_source', function ($encryptedId) {
            return LeadSource::where('id', EncryptionHelper::decrypt($encryptedId))
                ->when(Auth::user()->hasRole(['Admin', 'Superadmin']), function ($query) {
                    $query->withTrashed();
                })
                ->firstOrFail();
        });

        Route::bind('workspace', function ($encryptedId) {
            return Workspace::where('id', EncryptionHelper::decrypt($encryptedId))->firstOrFail();
        });

        Route::bind('client', function ($encryptedId) {
            return Client::select('clients.*')->with(['country'])->where('id', EncryptionHelper::decrypt($encryptedId))->where('workspace_id', Auth::user()->workspace_id)->withTrashed()->firstOrFail();
        });

        Route::bind('invoice', function ($encryptedId) {
            return Invoice::query()
                ->select('invoices.*')
                ->where('id', EncryptionHelper::decrypt($encryptedId))
                ->where('type', InvoiceType::INVOICE)
                ->whereHas('client', function ($q) {
                    $q->where('workspace_id', Auth::user()->workspace_id);
                })
                ->withTrashed()
                ->firstOrFail();
        });

        Route::bind('credit_note', function ($encryptedId) {
            return Invoice::query()
                ->select('invoices.*')
                ->where('id', EncryptionHelper::decrypt($encryptedId))
                ->where('type', InvoiceType::CREDIT_NOTE)
                ->whereHas('client', function ($q) {
                    $q->where('workspace_id', Auth::user()->workspace_id);
                })
                ->withTrashed()
                ->firstOrFail();
        });

        Route::bind('expense', function ($encryptedId) {
            return Expense::findOrFail(EncryptionHelper::decrypt($encryptedId));
        });

        Route::bind('deleted_expense', function ($encryptedId) {
            return Expense::onlyTrashed()->findOrFail(EncryptionHelper::decrypt($encryptedId));
        });

        Route::bind('any_expense', function ($encryptedId) {
            return Expense::withTrashed()->findOrFail(EncryptionHelper::decrypt($encryptedId));
        });

        Route::bind('expense_note', function ($encryptedId) {
            return ExpenseNote::where('id', EncryptionHelper::decrypt($encryptedId))->firstOrFail();
        });

        Route::bind('follow_up', function ($encryptedId) {
            return FollowUp::withTrashed()->findOrFail(EncryptionHelper::decrypt($encryptedId));
        });

        Route::bind('email_signature', function ($encryptedId) {
            return EmailSignature::findOrFail(EncryptionHelper::decrypt($encryptedId));
        });

        Route::bind('bank', function ($encryptedId) {
            return Bank::findOrFail(EncryptionHelper::decrypt($encryptedId));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
