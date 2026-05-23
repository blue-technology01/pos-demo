<?php

namespace App\Services\Auth;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class RegisterService
{
    // register user
    public function registerUser(array $data): array
    {
        // Check duplicate email
        if (User::where('email', $data['email'])->exists()) {
            return [
                'success' => false,
                'field'   => 'email',
                'message' => 'Email already exists'
            ];
        }

        try {
            return DB::transaction(function () use ($data) {

                $user = User::create([
                    'name'     => trim($data['name']),
                    'email'    => strtolower(trim($data['email'])),
                    'phone'    => $data['phone'] ?? null,
                    'password' => Hash::make($data['password']),
                    'avatar'   => $data['avatar'] ?? null, // must be uploaded before
                ]);

                // Assign role if provided
                if (!empty($data['role'])) {
                    $user->assignRole($data['role']);
                }

                return [
                    'success' => true,
                    'user'    => $user
                ];
            });

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ];
        }
    }

    public function updateUser(User $user, array $data): array
    {
        try {
            return DB::transaction(function () use ($user, $data) {

                $updateData = [
                    'name'  => trim($data['name']),
                    'email' => strtolower(trim($data['email'])),
                    'phone' => $data['phone'] ?? null,
                    'role'  => $data['role'] ?? null,
                ];

                // Only update password if provided
                if (!empty($data['password'])) {
                    $updateData['password'] = Hash::make($data['password']);
                }

                // Update avatar if uploaded
                if (isset($data['avatar'])) {
                    $updateData['avatar'] = $data['avatar'];
                }

                $user->update($updateData);

                // Sync role
                if (!empty($data['role'])) {
                    $user->syncRoles([$data['role']]);
                }

                return [
                    'success' => true,
                    'user'    => $user->fresh('roles')
                ];
            });

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Update failed. Please try again.'
            ];
        }
    }

    // get all role for dropdown
    public function getRoles()
    {
        return Role::select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    public function getUsers(Request $request)
    {
        // per_page for limit request
        $perPage = $request->input('per_page', 25);

        return User::query()
            ->with('roles:id,name')
            ->select('id', 'name', 'email', 'phone', 'avatar', 'created_at')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('phone', 'LIKE', "%{$search}%");
                });
            })
            ->latest()
            ->paginate((int)$perPage);
    }

    // update last login time
    public function updateLastLogin(User $user): void
    {
        User::where('id', $user->id)
            ->update(['last_login_at' => now()]);
    }

    /**
     * Delete user safely
    */
    public function deleteUser(int $id): array
    {
        try {
            // find target user
            $user = User::findOrFail($id);
            $currentUser=Auth::user();

            // only admin can be remove acount
            if (!$currentUser || !$currentUser->hasRole('admin')) {
                return [
                    'success' => false,
                    'message' => 'Unauthorized. Only administrators can delete users.'
                ];
            }

            // protection prevent self-deletion
            if($user->id === $currentUser->id){
                return [
                    'success' => false,
                    'message' => 'You cannot delete your own account.'
                ];
            }

            // Prevent deleting other admins
            if($user->hasRole('admin')){
                return [
                    'success'=>false,
                    'message'=>'Administrative accounts with full access cannot be deleted.'
                ];
            }

            // execute atomic database transactions
            $deleted = DB::transaction(function() use ($user) {
                $user->syncRoles([]);
                return $user->delete();
            });

            return [
                'success' => (bool)$deleted,
                'message' => 'User account has been permanently removed.'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ];
        }
    }
}
