<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdateUserRequest;
use App\Models\User;
use App\Services\Auth\RegisterService;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{

    public function __construct(
        protected RegisterService $registerService
    ) {}

    // Show user
    public function showFormRegister(Request $request)
    {
        $roles = $this->registerService->getRoles();

        $users = $this->registerService->getUsers($request);

        // AJAX request (table refresh / search)
        if ($request->ajax()) {
            try {
                return response()->json([
                    'users' => $users->items(),
                    'pagination' => [
                        'page'  => $users->currentPage(),
                        'last'  => $users->lastPage(),
                        'total' => $users->total(),
                    ]
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'error'   => 'Failed to load users.',
                    'message' => $e->getMessage()
                ], 500);
            }
        }

        // Blade page load
        return view('admin.users.user', compact('users', 'roles'));
    }

    // Handle User Registration
    public function register(RegisterRequest $request)
    {
        $key = 'register:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many registration attempts.'
            ], 429);
        }

        $data = $request->validated();

        Log::info('Avatar Debug:', [
            'hasFile' => $request->hasFile('avatar'),
            'file_name' => $request->file('avatar')?->getClientOriginalName(),
            'validated_avatar' => $data['avatar'] ?? 'NOT_IN_VALIDATED',
        ]);

        // Handle Avatar Upload
        $data['avatar'] = $request->hasFile('avatar')
            ? $request->file('avatar')->store('avatars', 'public')
            : null;

        $result = $this->registerService->registerUser($data);

    }

    // Update User
    public function update(UpdateUserRequest $request, User $user)
    {
        $key = 'update:user:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['success' => false, 'message' => 'Too many attempts.'], 429);
        }

        $data = $request->validated();

        // Handle Avatar
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } else {
            unset($data['avatar']);
        }

        $result = $this->registerService->updateUser($user, $data);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 422);
        }

        RateLimiter::clear($key);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully!',
            'user'    => $result['user']
        ]);
    }

    public function destroy(Request $request,int $id, RegisterService $registerService){
        $result=$registerService->deleteUser((int)$id);
        if(!$result['success']){
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ],422);
        };
        return response()->json([
            'success' => true,
            'message' => $result['message']
        ],200);
    }
}
