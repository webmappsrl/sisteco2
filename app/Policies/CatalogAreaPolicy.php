<?php

namespace App\Policies;

use App\Models\CatalogArea;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CatalogAreaPolicy
{

    public function before()
    {
        return true;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CatalogArea $catalogArea)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CatalogArea $catalogArea)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CatalogArea $catalogArea)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CatalogArea $catalogArea)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CatalogArea $catalogArea)
    {
        //
    }
}
