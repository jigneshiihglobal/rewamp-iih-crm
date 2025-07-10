<?php

namespace App\Providers;

use App\Enums\SalesInvoice;
use App\View\Composers\CountriesComposer;
use App\View\Composers\CurrenciesComposer;
use App\View\Composers\RoleComposer;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer([
            'users.modals.create',
            'profile.index'
        ], RoleComposer::class);

        // Custom Blade directive to check user's workspace
        Blade::if('workspace', function ($expression) {
            // Parse the expression to get the workspace slugs
            $slugs = explode('|', $expression);

            // Get the currently logged in user's workspace
            $user = auth()->user();
            $workspace = $user->active_workspace;

            // Check if the user's workspace slug is in the given slugs
            return in_array($workspace->slug, $slugs);
        });

        View::composer([
            'clients.modals.create',
            'clients.modals.edit'
        ], CountriesComposer::class);

        View::composer([
            'leads.index',
            'invoices.create-one-off',
            'invoices.subscription.create',
            'expenses.create-many',
            'expenses.create',
            'expenses.edit',
            'credit_notes.create',
        ], CurrenciesComposer::class);

        View::composer('partials.menu', function ($view) {
            $not_linked = \App\Models\PaymentDetail::where('is_invoice_link_to_crm', '0')->where('workspace_id',Auth::user()->workspace_id)->count();
            $pending_invoice = \App\Models\SalesUserInvoice::with(['client', 'company_detail'])
                            ->whereHas('client', function ($q) {
                                $q->where('workspace_id', Auth::user()->workspace_id);
                            })->where('status', SalesInvoice::MAIL_SEND)->count();

            $view->with('not_linked', $not_linked)
                ->with('pending_invoice', $pending_invoice);

        });
    }
}
