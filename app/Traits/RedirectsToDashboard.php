<?php

namespace App\Traits;
use Illuminate\Support\Facades\Auth;

/**
 * This trait is used for defining redirect to logic
 */
trait RedirectsToDashboard
{
    public function redirectTo()
    {
        if(Auth::user()->hasRole('Superadmin')){
            return route('invoices.index');
        }elseif (Auth::user()->hasRole('User')){
            return route('dashboard');
        }else{
            return route('leads.index');
        }
    }
}
