<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RoleComposer
{
    public function compose(View $view)
    {
        $view->with('roles', Role::all());
    }
}
