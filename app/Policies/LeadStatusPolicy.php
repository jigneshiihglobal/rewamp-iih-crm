<?php

namespace App\Policies;

use App\Models\LeadStatus;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeadStatusPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LeadStatus  $leadStatus
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, LeadStatus $leadStatus)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->hasRole(['Admin', 'Superadmin']);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LeadStatus  $lead_status
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, LeadStatus $lead_status)
    {
        return $user->hasRole(['Admin', 'Superadmin']);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LeadStatus  $lead_status
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, LeadStatus $lead_status)
    {
        return $user->hasRole(['Admin', 'Superadmin']);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LeadStatus  $leadStatus
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, LeadStatus $leadStatus)
    {
        return $user->hasRole(['Admin', 'Superadmin']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LeadStatus  $leadStatus
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, LeadStatus $leadStatus)
    {
        return $user->hasRole(['Admin', 'Superadmin']);
    }
}
