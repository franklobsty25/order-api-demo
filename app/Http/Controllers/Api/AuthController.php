<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use HttpResponses;

    /**
     * Register user.
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'unique:users'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->error(
                $errors->all(),
                'Invalid input data.'
            );
        }

        $user = User::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return $this->success([
            'user' => $user,
            'token' => $user->createToken($user->email)->plainTextToken,
        ]);

    }

    /**
     * Login user
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->error($errors->all());
        }

        $user = User::where('email', $request->email)->first();

        if (is_null($user)) {
            return $this->error(null, 'User not found');
        }

        $isValid = Hash::check($request->password, $user->password);

        if (!$isValid) {
            throw ValidationException::withMessages(['password' => 'Password don\'t match.']);
        }

        Auth::login($user, $request->remember_me);

        $token = $user->createToken($user->email)->plainTextToken;

        return $this->success(['user' => $user, 'token' => $token]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $email): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->error($errors->all());
        }

        $user = User::where('email', $email)->update([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
        ]);

        if (!$user) {
            return $this->error(null, 'User not found', 404);
        }

        return $this->success(['success' => true], 'User record updated successfully.', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $user): JsonResponse
    {
        try {
            User::where('id', $user)->delete();

            return $this->success(
                ['isDeleted' => true],
                'User record deleted successfully.',
                200
            );
        } catch (\Exception $e) {
            return $this->error(
                $e->getMessage(),
                'User deletion unsuccessful.',
                500
            );
        }

    }


    public function list(Request $request)
    {
        $query = $request->query();

        $page = $query['page'] ?? 1;
        $perPage = $query['perPage'] ?? 15;
        $search = $query['search'] ?? '';
        $all = $query['all'] ?? false;

        $users = User::orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'users', $page);

        if ($search)
            $users = User::where('firstname', 'like', "%$search%")
                ->orWhere('lastname', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%")
                ->orderBy('created_at', 'asc')->paginate();

        if ($all)
            $users = User::orderByDesc('created_at')->get();

        return UserResource::collection($users);
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success(
            new UserResource($request->user()),
            'User retrieved successfully.',
            200
        );
    }

    public function countUsers(): JsonResponse
    {
        $count = User::count();

        return $this->success(
            ['totalUsers' => $count],
            'Users counted successfully.',
            200,
        );
    }
}
