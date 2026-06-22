<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdateUserRequest;
use App\Models\User;
use App\Services\Auth\RegisterService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function __construct(
        protected RegisterService $registerService
    ) {}

    public function showFormRegister(Request $request)
    {
        $users = $this->registerService->getAllUsers($request);
        $roles = $this->registerService->getRoles();

        return view('admin.users.user', compact('users', 'roles'));
    }

    // show user profile
    public function userProfile() {
        $users = $this->registerService->getAllUsers();
        $roles = $this->registerService->getRoles();
        return view('admin.users.user-profile',compact('users','roles'));
    }
    // function for preview setting
    public function previewSetting() {
        return view('admin.users.preview-settings');
    }
    public function register(RegisterRequest $request)
    {
        $key = 'register:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            return response()->json(['success' => false, 'message' => 'Too many attempts.'], 429);
        }

        RateLimiter::hit($key, 60);

        $data = $request->validated();
        $data['avatar'] = $request->hasFile('avatar')
            ? $request->file('avatar')->store('avatars', 'public')
            : null;

        $result = $this->registerService->registerUser($data);

        return $result['success']
            ? response()->json(['success' => true, 'message' => 'User created!', 'user' => $this->formatUserResponse($result['user'])])
            : response()->json(['success' => false, 'message' => $result['message']], 422);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();
        if (empty($data['password'] ?? '')) {
            unset($data['password'], $data['password_confirmation']);
        }

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $result = $this->registerService->updateUser($user, $data);
        return $result['success']
            ? response()->json(['success' => true, 'message' => 'User updated!', 'user' => $this->formatUserResponse($result['user'])])
            : response()->json(['success' => false, 'message' => $result['message']], 422);
    }

    public function destroy(int $id)
    {
        $result = $this->registerService->deleteUser($id);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'id'      => $id,
        ], $result['success'] ? 200 : 422);
    }

    /* Helper to keep response consistent */
    private function formatUserResponse(User $user): array
    {
        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'phone'      => $user->phone,
            'avatar'     => $user->avatar,
            'created_at' => $user->created_at->timestamp,
            'roles'      => $user->roles->map(fn($r) => ['name' => $r->name]),
        ];
    }
}
