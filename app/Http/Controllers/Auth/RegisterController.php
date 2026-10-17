<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisterController extends Controller
{
    /**
     * Display the registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request.
     */
    public function register(Request $request)
    {
        $validationRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:trader,analyst'],
        ];

        // Trader-specific fields
        if ($request->role === 'trader') {
            $validationRules += [
                'experience_level' => ['nullable', 'in:beginner,intermediate,advanced'],
                'trading_style' => ['nullable', 'string', 'max:100'],
                'specialization' => ['nullable', 'string', 'max:100'],
                'preferred_sessions' => ['nullable', 'array'],
                'favorite_pairs' => ['nullable', 'array'],
            ];
        }

        $request->validate($validationRules);

        // Prepare user data with common fields
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verified_at' => null, // Requires standard email verification
            'verification_status' => $request->role === 'trader' ? 'verified' : 'pending',
            'is_active' => true,
            // Common profile fields
            'country' => $request->country ?: null,
            'timezone' => $request->timezone ?: 'UTC',
            'profile_visibility' => 'public',
        ];

        // Auto-generate username if not provided
        if (empty($userData['username'])) {
            $cleanName = preg_replace('/[^a-z0-9_]/', '', strtolower(str_replace(' ', '_', $request->name)));
            $base = substr($cleanName ?: 'trader', 0, 40);
            $candidate = $base . '_' . substr(md5(uniqid()), 0, 4);
            while (User::where('username', $candidate)->exists()) {
                $candidate = $base . '_' . substr(md5(uniqid()), 0, 6);
            }
            $userData['username'] = $candidate;
        }

        $user = User::create($userData);

        // Assign selected role
        $user->assignRole($request->role);

        // Dispatch standard Laravel registered event (triggers verification email notification)
        event(new Registered($user));

        Auth::login($user);

        // Redirect newly registered users directly to email verification notice
        return redirect()->route('verification.notice');
    }
}
