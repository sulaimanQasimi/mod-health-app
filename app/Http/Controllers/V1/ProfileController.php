<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user();
        $user->load([
            'roles:id,name,name_dr',
            'branch:id,name',
            'department:id,name',
            'section:id,name',
            'category:id,name',
        ]);

        return Inertia::render('Profile/Show', [
            'profile' => $this->transformProfile($user),
            'defaultAvatar' => asset('assets/img/avatars/1.png'),
            'urls' => [
                'update' => route('react.profile.update'),
                'updatePassword' => route('react.profile.update-password'),
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->only(['name', 'last_name', 'email']);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return redirect()
            ->route('react.profile.show')
            ->with('success', localize('global.user_update_success'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => localize('global.current_password_incorrect'),
            ]);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return redirect()
            ->route('react.profile.show')
            ->with('success', localize('global.password_updated_success'));
    }

    /**
     * @return array<string, mixed>
     */
    private function transformProfile(User $user): array
    {
        $joinedAt = null;
        if ($user->created_at) {
            try {
                $joinedAt = verta($user->created_at)->format('Y/m/d');
            } catch (\Throwable) {
                $joinedAt = $user->created_at->format('Y-m-d');
            }
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'avatar_url' => $user->avatar
                ? asset('storage/'.$user->avatar)
                : asset('assets/img/avatars/1.png'),
            'status' => (int) $user->status,
            'is_doctor' => (bool) $user->is_doctor,
            'clinic_type' => $user->clinic_type,
            'branch_name' => $user->branch?->name,
            'department_name' => $user->department?->name,
            'section_name' => $user->section?->name,
            'category_name' => $user->category?->name,
            'roles' => $user->roles->map(fn ($role) => [
                'id' => $role->id,
                'name' => $role->name_dr ?? $role->name,
            ])->values()->all(),
            'joined_at' => $joinedAt,
        ];
    }
}
