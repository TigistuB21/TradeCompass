<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    /**
     * Display the email verification notice
     */
    public function notice(Request $request)
    {
        if ($request->user() && $request->user()->hasVerifiedEmail()) {
            return $this->redirectVerifiedUser($request->user());
        }

        return view('auth.verify-email');
    }

    /**
     * Handle email verification
     */
    public function verify(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->redirectVerifiedUser($request->user());
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return $this->redirectVerifiedUser($request->user())
            ->with('success', 'Email verified successfully! Welcome to TradeCompass.');
    }

    /**
     * Resend verification email
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->redirectVerifiedUser($request->user());
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }

    /**
     * Redirect a verified user based on role and onboarding status
     */
    private function redirectVerifiedUser($user)
    {
        // Check if analyst needs to complete application
        if ($user->hasRole('analyst') && !$user->application_id) {
            return redirect()->route('analyst-application.create')
                ->with('info', 'Email verified! Please complete your analyst application to get verified.');
        }

        // Check if account is pending admin verification
        if ($user->verification_status === 'pending') {
            return redirect()->route('verification.pending')
                ->with('info', 'Email verified! Your account is pending admin approval.');
        }

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('analyst')) {
            return redirect()->route('analyst.dashboard');
        } else {
            return redirect()->route('trader.dashboard');
        }
    }
}
