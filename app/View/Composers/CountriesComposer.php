<?php

namespace App\View\Composers;

use App\Models\Country;
use Illuminate\View\View;

class CountriesComposer
{
    public function compose(View $view)
    {
        $view->with('countries', Country::all());
    }
}
