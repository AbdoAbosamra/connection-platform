<?php

namespace App\Services;

use App\Models\EmployerProfile;
use App\Models\JobSeekerProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'role'     => $data['role'],
                'country'  => $data['country'] ?? null,
            ]);

            match ($user->role) {
                'employer'   => EmployerProfile::create([
                    'user_id'      => $user->id,
                    'company_name' => $data['company_name'],
                ]),
                'job_seeker' => JobSeekerProfile::create([
                    'user_id' => $user->id,
                ]),
                default => null,
            };

            $token = $user->createToken('auth_token')->plainTextToken;

            return ['user' => $user->load('employerProfile', 'jobSeekerProfile'), 'token' => $token];
        });
    }

    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been suspended.'],
            ]);
        }

        $user->update(['last_seen_at' => now()]);
        $token = $user->createToken('auth_token')->plainTextToken;

        return ['user' => $user->load('employerProfile', 'jobSeekerProfile'), 'token' => $token];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
