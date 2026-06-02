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

     /* user list  */
    public function showFormRegister(Request $request)
    {
        $users = User::with(['roles:id,name'])
            ->latest()
            ->paginate(10);

        $roles = $this->registerService->getRoles();

        return view('admin.users.user', compact('users', 'roles'));
    }

    public function register(RegisterRequest $request)
    {
        $key = 'register:' . $request->ip();
        RateLimiter::hit($key, 60);

        if (RateLimiter::tooManyAttempts($key, 3)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many attempts. Please wait a moment.',
            ], 429);
        }

        $data = $request->validated();

        $data['avatar'] = $request->hasFile('avatar')
            ? $request->file('avatar')->store('avatars', 'public')
            : null;

        $result = $this->registerService->registerUser($data);

        if ($result['success']) {
            RateLimiter::clear($key);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully!',
                'html' => view('admin.users.partials.user-row', [
                    'user' => $result['user'],
                ])->render(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Failed to create user',
        ], 422);
    }

    /*
        update user
    */
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password'], $data['password_confirmation']);
        }

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $result = $this->registerService->updateUser($user, $data);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'User updated successfully!',
                'user' => $result['user'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 422);
    }

    public function updatePreview(Request $request)
    {
        $user = User::findOrFail($request->user_id);

        $user->preview_receipt = $request->preview;

        $user->save();

        return response()->json([
            'success' => true
        ]);
    }

    /*
        remove user
    */
    public function destroy(int $id)
    {
        $result = $this->registerService->deleteUser($id);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'id' => $id
        ], $result['success'] ? 200 : 422);
    }
}
