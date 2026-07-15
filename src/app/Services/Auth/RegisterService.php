<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

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

    // get all user
    public function getAllUsers(Request $request): LengthAwarePaginator
    {
        return User::with('roles')
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%");
            })
            ->when($request->role, function ($q) use ($request) {
                $q->whereHas('roles', function ($r) use ($request) {
                    $r->where('name', $request->role);
                });
            })
            ->latest()
            ->paginate($request->per_page ?? 25)
            ->withQueryString();
    }

    // query user
    public function queryUsers(Request $request) {
        return User::with('roles')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;

                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), function ($q) use ($request) {
                $q->whereHas('roles', fn($r) => $r->where('name', $request->role));
            })
            ->latest();
    }

    // get all role name
    public function getRoleName(User $user): string
    {
        return $user->roles->first()?->name ?? 'none';
    }

    // clear cache ater create , update remove
    public function clearCache(): void
    {
        Cache::forget('users:all');
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
