<?php

namespace App\Http\Services;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserServices
{

    public function createUser(Request $request)
    {

        try {

            $validation = $request->validate([
                'name' => ['required'],
                'email' => ['required', 'email'],
                'password' => ['required'],
                'role' => ['required', Rule::in(['user', 'admin'])]
            ]);

            $checkingEmail = User::where('email', $validation['email'])->first();

            if ($checkingEmail) return response_return('Email already registered!', [
                'name' => $checkingEmail->name,
                'email' => $checkingEmail->email,
            ], 409);

            $validation['password'] = Hash::make($validation['password']);

            $createUser = User::create($validation);

            if (!$createUser) return response_return('Something is wrong in validation and user creation.', [], 409);

            return response_return('Succesfully created user.', [
                'id' => $createUser->id,
                'name' => $createUser->name,
                'email' => $createUser->email,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error occurred during the execution of creating a user.',
                'data' => []
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $validation = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required']
            ]);

            $checkingUser = User::where('email', $validation['email'])->first();

            if (!$checkingUser || !Hash::check($validation['password'], $checkingUser->password)) {
                return response_return('User might not be registered or password is incorrect. Please check again.', [], 409);
            }

            $token = $checkingUser->createToken('auth_token')->plainTextToken;

            return response_return('Successfully login.', [
                'token' => $token,
                'id' => $checkingUser->id,
                'name' => $checkingUser->name,
                'email' => $checkingUser->email,
                'role' => $checkingUser->role
            ], 200);
        } catch (\Throwable $th) {
            return response_return('Error occurred during the execution of creating a user.', [], 501);
        }
    }

    public function updateUser(Request $request)
    {
        try {
            $validation = $request->validate([
                'id' => ['required']
            ]);

            $getUser = User::where('id', $validation['id'])->first();

            if (!$getUser) return response_return('Cannot find the user to update.', [], 409);

            $updateUser = $getUser->update($request->only(['name', 'email', 'role']));

            if (!$updateUser) return response_return('Cannot save/update user information to this moment.', [], 409);

            return response_return('Successfully updated user information.', [
                'name' => $getUser->name,
                'email' => $getUser->email,
            ], 201);
        } catch (\Throwable $th) {
            return response_return('Error occurred during the execution of updating a user.', [], 500);
        }
    }

    public function isAuthenticated()
    {
        return auth()->check();
    }
}
