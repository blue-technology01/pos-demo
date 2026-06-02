<?php

namespace App\Services\Auth;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RegisterService
{
    public function getRoles()
    {
        return Role::orderBy('name')->get();
    }

    /* =========================
       REGISTER USER (AJAX SAFE)
    ========================== */
    public function registerUser(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {

                $user = User::create([
                    'name'     => trim($data['name']),
                    'email'    => strtolower(trim($data['email'])),
                    'phone'    => $data['phone'] ?? null,
                    'password' => Hash::make($data['password']),
                    'avatar'   => $data['avatar'] ?? null,
                ]);

                if (!empty($data['role'])) {
                    $user->syncRoles([$data['role']]);
                }

                return [
                    'success' => true,
                    'user'    => $user->load('roles')
                ];
            });

        } catch (\Throwable $e) {

            if (!empty($data['avatar'])) {
                Storage::disk('public')->delete($data['avatar']);
            }

            report($e);

            return [
                'success' => false,
                'message' => 'Failed to create user. Please try again.'
            ];
        }
    }

    /* =========================
       UPDATE USER (AJAX SAFE)
    ========================== */
    public function updateUser(User $user, array $data): array
    {
        try {
            return DB::transaction(function () use ($user, $data) {

                if (!empty($data['avatar']) && $user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }

                $updateData = [
                    'name'  => trim($data['name']),
                    'email' => strtolower(trim($data['email'])),
                    'phone' => $data['phone'] ?? null,
                ];

                if (!empty($data['password'])) {
                    $updateData['password'] = Hash::make($data['password']);
                }

                if (!empty($data['avatar'])) {
                    $updateData['avatar'] = $data['avatar'];
                }

                $user->update($updateData);

                if (!empty($data['role'])) {
                    $user->syncRoles([$data['role']]);
                }

                return [
                    'success' => true,
                    'user'    => $user->fresh('roles')
                ];
            });

        } catch (\Throwable $e) {

            if (!empty($data['avatar'])) {
                Storage::disk('public')->delete($data['avatar']);
            }

            report($e);

            return [
                'success' => false,
                'message' => 'Failed to update user. Please try again.'
            ];
        }
    }

    /* =========================
       DELETE USER (AJAX SAFE)
    ========================== */
    public function deleteUser(int $id): array
    {
        $user = User::find($id);
        $currentUser = Auth::user();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found.'
            ];
        }

        if ($currentUser && $user->id === $currentUser->id) {
            return [
                'success' => false,
                'message' => 'You cannot delete your own account.'
            ];
        }

        if ($user->hasRole('admin')) {
            return [
                'success' => false,
                'message' => 'Administrator accounts cannot be deleted.'
            ];
        }

        DB::transaction(function () use ($user) {

            $user->syncRoles([]);

            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->delete();
        });

        return [
            'success' => true,
            'message' => 'User deleted successfully.'
        ];
    }
}
