<?php

namespace App\Services\Auth;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;

class RegisterService
{
    // get all role of user
    public function getRoles(): Collection
    {
        $data = Cache::remember('auth:roles', 300, fn () =>
            Role::select('id', 'name')
                ->orderBy('name')
                ->get()
                ->toArray()
        );

        return Role::hydrate($data);
    }

    public function getAllUsers(): Collection
    {
        $data = Cache::remember('users:all', 60, function () {
            return User::with(['roles:id,name'])
                ->latest()
                ->get(['id', 'name', 'email', 'phone', 'avatar', 'created_at'])
                ->map(fn ($user) => [
                    'id'         => $user->id,
                    'name'       => $user->name,
                    'email'      => $user->email,
                    'phone'      => $user->phone,
                    'avatar'     => $user->avatar,
                    'created_at' => $user->created_at,
                    'roles'      => $user->roles->toArray(),
                ])
                ->values()
                ->toArray();
        });

        // Hydrate users from cached arrays and restore the roles relation
        $users = User::hydrate($data);

        foreach ($users as $idx => $user) {
            $rolesData = $data[$idx]['roles'] ?? [];
            $roleModels = Role::hydrate($rolesData ?: []);

            // Ensure there is no raw 'roles' attribute that would shadow the relation
            $attrs = $user->getAttributes();
            if (array_key_exists('roles', $attrs)) {
                unset($attrs['roles']);
                $user->setRawAttributes($attrs, true);
            }

            $user->setRelation('roles', $roleModels);
        }

        return $users;
    }
    // Role helper
    public function getRoleName(User $user): string
    {
        return $user->roles->first()?->name ?? 'none';
    }

    // clear cache ater create , update remove
    public function clearCache(): void
    {
        Cache::forget('users:all');
        // Cache::forget('auth:roles'); // uncomment if roles change frequently
    }

    // function for register user
    public function registerUser(array $data): array
    {
        try {
            $result = DB::transaction(function () use ($data) {
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

                $user->load('roles');

                return [
                    'success' => true,
                    'user'    => $user,
                ];
            });

            $this->clearCache();

            return $result;

        } catch (\Throwable $e) {
            if (!empty($data['avatar'])) {
                Storage::disk('public')->delete($data['avatar']);
            }
            report($e);
            return ['success' => false, 'message' => 'Failed to create user.'];
        }
    }
    // function for update user
    public function updateUser(User $user, array $data): array
    {
        try {
            $result = DB::transaction(function () use ($user, $data) {
                if (!empty($data['avatar']) && $user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }

                $update = [
                    'name'  => trim($data['name']),
                    'email' => strtolower(trim($data['email'])),
                    'phone' => $data['phone'] ?? null,
                ];

                if (!empty($data['password'])) {
                    $update['password'] = Hash::make($data['password']);
                }
                if (!empty($data['avatar'])) {
                    $update['avatar'] = $data['avatar'];
                }

                $user->update($update);

                if (!empty($data['role'])) {
                    $user->syncRoles([$data['role']]);
                }

                $user->load('roles');

                return ['success' => true, 'user' => $user];
            });

            $this->clearCache();

            return $result;

        } catch (\Throwable $e) {
            if (!empty($data['avatar'])) Storage::disk('public')->delete($data['avatar'] ?? '');
            report($e);
            return ['success' => false, 'message' => 'Failed to update user.'];
        }
    }
    // function for remove user
    public function deleteUser(int $id): array
    {
        $user = User::findOrFail($id);
        $current = Auth::user();

        if ($current && $user->id === $current->id) {
            return ['success' => false, 'message' => 'Cannot delete your own account.'];
        }
        if ($user->hasRole('admin')) {
            return ['success' => false, 'message' => 'Cannot delete admin account.'];
        }

        DB::transaction(function () use ($user) {
            $user->syncRoles([]);
            if ($user->avatar) Storage::disk('public')->delete($user->avatar);
            $user->delete();
        });

        $this->clearCache();

        return ['success' => true, 'message' => 'User deleted.'];
    }
}
