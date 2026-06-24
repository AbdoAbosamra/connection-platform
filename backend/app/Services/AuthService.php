<?php

namespace App\Services;

use App\Models\EmployerProfile;
use App\Models\JobSeekerProfile;
use App\Models\User;
use App\Services\Verification\VerificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; // kept for login password verification
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(private VerificationService $verification) {}

    public function register(array $data): array
    {
        // Disposable / throwaway employer emails are rejected outright — they can
        // never represent a real business, so they shouldn't even hold an account.
        if (($data['role'] ?? null) === 'employer' && $this->verification->isDisposable($data['email'])) {
            throw ValidationException::withMessages([
                'email' => ['Please register with a permanent company email address.'],
            ]);
        }

        return DB::transaction(function () use ($data) {
            // Do NOT call Hash::make() here — the User model already has
            // 'password' => 'hashed' cast which hashes automatically on set.
            // Passing Hash::make() output to a 'hashed' cast causes double-hashing,
            // which makes the stored hash unverifiable and prevents all logins.
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],  // cast handles bcrypt
                'role' => $data['role'],
                'country' => $data['country'] ?? null,
            ]);

            if ($user->role === 'employer') {
                $employer = EmployerProfile::create([
                    'user_id' => $user->id,
                    'company_name' => $data['company_name'],
                    'job_post_credits' => 10,
                ]);
                // Corporate-domain employers are auto-verified instantly; everyone
                // on free/personal email stays unverified until they prove themselves
                // (LinkedIn OAuth or a payment authorization) before they can post.
                $this->verification->autoVerify($employer, $user->email);
            } elseif ($user->role === 'job_seeker') {
                JobSeekerProfile::create(['user_id' => $user->id]);
            }

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

        // Revoke all previous tokens before issuing a new one.
        // Without this, every login accumulates a new valid token in the DB;
        // a compromised old token would remain valid indefinitely.
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return ['user' => $user->load('employerProfile', 'jobSeekerProfile'), 'token' => $token];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
