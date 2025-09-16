<?php

namespace App\Policies;

use App\Models\NutritionCare;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NutritionCarePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_nutrition_care') || 
               $user->hasRole(['admin', 'super_admin', 'nurse', 'doctor']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, NutritionCare $nutritionCare): bool
    {
        return $user->hasPermissionTo('view_nutrition_care') || 
               $user->hasRole(['admin', 'super_admin', 'nurse', 'doctor']) ||
               $user->id === $nutritionCare->created_by;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_nutrition_care') || 
               $user->hasRole(['admin', 'super_admin', 'nurse']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, NutritionCare $nutritionCare): bool
    {
        return $user->hasPermissionTo('edit_nutrition_care') || 
               $user->hasRole(['admin', 'super_admin']) ||
               ($user->hasRole('nurse') && $user->id === $nutritionCare->created_by);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, NutritionCare $nutritionCare): bool
    {
        return $user->hasPermissionTo('delete_nutrition_care') || 
               $user->hasRole(['admin', 'super_admin']) ||
               ($user->hasRole('nurse') && $user->id === $nutritionCare->created_by);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, NutritionCare $nutritionCare): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, NutritionCare $nutritionCare): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }
}
