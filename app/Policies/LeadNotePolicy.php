<?php

namespace App\Policies;

use App\Models\LeadNote;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class LeadNotePolicy
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
     * @param  \App\Models\LeadNote  $leadNote
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, LeadNote $leadNote)
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
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LeadNote  $leadNote
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, LeadNote $leadNote)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LeadNote  $leadNote
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, LeadNote $leadNote)
    {

        return $user->hasRole(['Admin', 'Superadmin']) || $leadNote->user_id == $user->id
            ? Response::allow('User can delete the lead note')
            : Response::deny('You are not authorized to delete this lead note');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LeadNote  $leadNote
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, LeadNote $leadNote)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LeadNote  $leadNote
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, LeadNote $leadNote)
    {
        //
    }
}
