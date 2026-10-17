<?php

use Illuminate\Support\Facades\Route;

// Auth
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\EmailVerificationController;

// Analyst Application
use App\Http\Controllers\AnalystApplicationController;

// General
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;

// Admin Controllers
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\VerificationController as AdminVerificationController;
use App\Http\Controllers\Admin\AssignmentController;
use App\Http\Controllers\Admin\AdminAnalystRequestController;
use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\SystemSettingsController;

// Analyst Controllers
use App\Http\Controllers\Analyst\AnalystDashboardController;
use App\Http\Controllers\Analyst\FeedbackController as AnalystFeedbackController;
use App\Http\Controllers\Analyst\FeedbackTemplateController;
use App\Http\Controllers\Analyst\AnalystPayoutController;
use App\Http\Controllers\Analyst\AnalystProfileController;
use App\Http\Controllers\Analyst\AnalystReviewController;

// Phase 1: Subscription Controllers
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\ChapaWebhookController;

// Trader Controllers
use App\Http\Controllers\Trader\TraderDashboardController;
use App\Http\Controllers\Trader\TraderAnalystRequestController;
use App\Http\Controllers\Trader\TradeController;
use App\Http\Controllers\Trader\StrategyController;
use App\Http\Controllers\Trader\TradeAccountController;
use App\Http\Controllers\Trader\AnalyticsController as TraderAnalyticsController;
use App\Http\Controllers\Trader\FeedbackController as TraderFeedbackController;
use App\Http\Controllers\Trader\AchievementController;
use App\Http\Controllers\Trader\LeaderboardController;

// Middleware
use App\Http\Middleware\EnsureVerified;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Guest Routes (Public)
Route::middleware('guest')->group(function () {
    // Registration
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Password Reset
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Analyst Application (Public - No Auth Required)
Route::get('/apply/analyst', [AnalystApplicationController::class, 'create'])
    ->name('analyst-application.create');
Route::post('/apply/analyst', [AnalystApplicationController::class, 'store'])
    ->name('analyst-application.store');
Route::get('/apply/analyst/success', [AnalystApplicationController::class, 'success'])
    ->name('analyst-application.success');

// EMERGENCY DB FIX ROUTE
Route::get('/debug-db-fix', function() {
    try {
        Illuminate\Support\Facades\DB::statement("
            CREATE TABLE IF NOT EXISTS analyst_applications (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                name VARCHAR NOT NULL,
                email VARCHAR NOT NULL UNIQUE,
                country VARCHAR NULL,
                timezone VARCHAR NULL,
                phone VARCHAR NULL,
                years_experience VARCHAR NOT NULL,
                certifications TEXT NULL,
                certificate_files TEXT NULL,
                methodology TEXT NULL,
                specializations TEXT NULL,
                coaching_experience VARCHAR NOT NULL,
                clients_coached VARCHAR NOT NULL,
                coaching_style VARCHAR NULL,
                track_record_url VARCHAR NULL,
                linkedin_url VARCHAR NULL,
                twitter_handle VARCHAR NULL,
                youtube_url VARCHAR NULL,
                website_url VARCHAR NULL,
                why_join TEXT NOT NULL,
                unique_value TEXT NOT NULL,
                max_clients VARCHAR NOT NULL,
                communication_methods TEXT NULL,
                status VARCHAR DEFAULT 'pending' CHECK(status IN ('pending', 'approved', 'rejected')) NOT NULL,
                rejection_reason TEXT NULL,
                reviewed_by INTEGER NULL,
                reviewed_at DATETIME NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                FOREIGN KEY(reviewed_by) REFERENCES users(id) ON DELETE SET NULL
            )
        ");
        
        // DIAGNOSTIC FIX
        $log = [];
        $log[] = "1. Checking 'users' table columns...";
        
        $columns = array_map(function($c) { return $c->name; }, Illuminate\Support\Facades\DB::select("PRAGMA table_info(users)"));
        $log[] = "   Found columns: " . implode(', ', $columns);
        
        $missing = [];
        $required = [
            'analyst_verification_status' => "ALTER TABLE users ADD COLUMN analyst_verification_status VARCHAR DEFAULT 'pending'",
            'verified_at' => "ALTER TABLE users ADD COLUMN verified_at DATETIME NULL",
            'verified_by' => "ALTER TABLE users ADD COLUMN verified_by INTEGER NULL",
            'application_id' => "ALTER TABLE users ADD COLUMN application_id INTEGER NULL",
            'specializations' => "ALTER TABLE users ADD COLUMN specializations TEXT NULL",
            'certifications' => "ALTER TABLE users ADD COLUMN certifications TEXT NULL"
        ];
        
        foreach ($required as $col => $sql) {
            if (!in_array($col, $columns)) {
                $log[] = "2. Column '$col' MISSING. Attempting to add...";
                try {
                    Illuminate\Support\Facades\DB::statement($sql);
                    $log[] = "   SUCCESS: Added '$col'.";
                } catch (\Exception $e) {
                    $log[] = "   ERROR Adding '$col': " . $e->getMessage();
                }
            } else {
                $log[] = "   OK: Column '$col' exists.";
            }
        }
        
        // Re-check
        $columnsAfter = array_map(function($c) { return $c->name; }, Illuminate\Support\Facades\DB::select("PRAGMA table_info(users)"));
        $log[] = "3. Final column list (count=" . count($columnsAfter) . "): " . implode(', ', $columnsAfter);
        
        return response(implode("\n", $log))->header('Content-Type', 'text/plain');

    } catch (\Exception $e) {
        return "FATAL ERROR: " . $e->getMessage();
    }
});

// Verification Pending (accessible to authenticated but unverified users)
Route::get('/verification/pending', function () {
    return view('auth.verification-pending');
})->middleware('auth')->name('verification.pending');

// Public Profile View (accessible to all)
Route::get('/profile/{username}', [ProfileController::class, 'show'])->name('profile.show');

// Phase 1: Public Analyst Marketplace
Route::get('/analysts', [AnalystProfileController::class, 'index'])->name('analysts.index');
Route::get('/analysts/{username}', [AnalystProfileController::class, 'show'])->name('analysts.show');
Route::post('/analysts/recommend', [\App\Http\Controllers\Analyst\AnalystRecommendationController::class, 'recommend'])->name('analysts.recommend');

// Chapa Webhook (public, no auth required)
Route::post('/chapa/webhook', [ChapaWebhookController::class, 'handle']);
Route::get('/chapa/callback', [SubscriptionController::class, 'chapaCallback'])->name('chapa.callback');

// CHAPA SIMULATION ROUTES (For local testing without API keys)
/*
Route::get('/test/chapa/checkout', function(\Illuminate\Http\Request $request) {
    if (config('services.chapa.mode') !== 'simulation') {
        abort(404);
    }
    
    $txRef = $request->get('tx_ref');
    $amount = $request->get('amount');
    $meta = $request->get('meta'); // encoded json
    
    return view('subscriptions.test-chapa-checkout', compact('txRef', 'amount', 'meta'));
})->name('test.chapa.checkout');
*/

/*
Route::post('/test/chapa/pay', function(\Illuminate\Http\Request $request, \App\Services\ChapaPaymentService $chapaService) {
    if (config('services.chapa.mode') !== 'simulation') {
        abort(404);
    }

    $txRef = $request->tx_ref;
    $amount = $request->amount;
    $meta = json_decode(urldecode($request->meta), true);
    
    // Simulate successful payment data
    $data = [
        'amount' => $amount,
        'currency' => 'ETB',
        'tx_ref' => $txRef,
        'reference' => 'SIM-' . uniqid(),
        'status' => 'success',
    ];
    
    // Process the payment
    $chapaService->processPaymentSuccess($txRef, $data, $meta);
    
    // Redirect to success page
    return redirect()->route('subscription.success', ['tx_ref' => $txRef]);
})->name('test.chapa.pay');
*/

/*
// TEMPORARY: Manual subscription creation for local testing (remove in production)
Route::get('/test/create-subscription/{analyst}/{trader}/{plan}', function($analystId, $traderId, $plan) {
    // Create subscription manually
    $subscription = \App\Models\Subscription::create([
        'analyst_id' => $analystId,
        'trader_id' => $traderId,
        'plan' => $plan,
        'price' => $plan === 'elite' ? 199 : ($plan === 'premium' ? 99 : 49),
        'status' => 'active',
        'chapa_tx_ref' => 'test_' . uniqid(),
        'current_period_start' => now(),
        'current_period_end' => now()->addMonth(),
    ]);

    // Create analyst assignment
    \App\Models\AnalystAssignment::firstOrCreate(
        ['analyst_id' => $analystId, 'trader_id' => $traderId],
        ['status' => 'active']
    );

    // Send notification to analyst
    $analyst = \App\Models\User::find($analystId);
    $trader = \App\Models\User::find($traderId);
    
    if ($analyst && $trader) {
        \App\Models\Notification::create([
            'user_id' => $analyst->id,
            'type' => 'new_subscription',
            'title' => 'New Subscriber!',
            'message' => "{$trader->name} subscribed to your " . ucfirst($plan) . " plan ($" . number_format($subscription->price, 2) . "/mo)",
            'data' => json_encode([
                'trader_id' => $trader->id,
                'trader_name' => $trader->name,
                'plan' => $plan,
                'price' => $subscription->price,
            ]),
        ]);
    }

    return redirect()->route('subscription.success')->with('success', 'Test subscription created!');
})->name('test.subscription.create');
*/


// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Analyst Assignment Requests
    Route::get('/analyst/request', function() {
        return redirect()->route('analysts.index')->with('info', 'We have moved to a self-service Marketplace! Please choose your analyst here.');
    })->name('trader.analyst-request.create');
    Route::post('/analyst/request', [TraderAnalystRequestController::class, 'store'])->name('trader.analyst-request.store');
    Route::delete('/analyst/request/{analystRequest}', [TraderAnalystRequestController::class, 'cancel'])->name('trader.analyst-request.cancel');
    Route::get('/analyst/consent/{analystRequest}', [TraderAnalystRequestController::class, 'showConsent'])->name('trader.analyst-request.consent');
    Route::post('/analyst/consent/{analystRequest}', [TraderAnalystRequestController::class, 'processConsent'])->name('trader.analyst-request.process-consent');

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Email Verification
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Notification Routes (Authenticated Users)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    // Profile Routes (Authenticated Users)
    Route::get('/settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/settings/profile/photo', [ProfileController::class, 'uploadPhoto'])->name('profile.upload-photo');
    Route::post('/settings/profile/cover', [ProfileController::class, 'uploadCover'])->name('profile.upload-cover');
    Route::delete('/settings/profile/photo', [ProfileController::class, 'deletePhoto'])->name('profile.delete-photo');
    Route::delete('/settings/profile/cover', [ProfileController::class, 'deleteCover'])->name('profile.delete-cover');

    // Admin Routes
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        
        // User Management
        Route::resource('users', UserManagementController::class);
        Route::post('/users/{user}/change-role', [UserManagementController::class, 'changeRole'])->name('users.change-role');
        Route::post('/users/{user}/deactivate', [UserManagementController::class, 'deactivate'])->name('users.deactivate');
        Route::post('/users/{user}/reactivate', [UserManagementController::class, 'reactivate'])->name('users.reactivate');
        Route::post('/users/{user}/ban', [UserManagementController::class, 'ban'])->name('users.ban');
        Route::post('/users/{user}/unban', [UserManagementController::class, 'unban'])->name('users.unban');
        Route::post('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('/users/{user}/reset-profile-photo', [UserManagementController::class, 'resetProfilePhoto'])->name('users.reset-profile-photo');
        Route::post('/users/{user}/reset-cover-photo', [UserManagementController::class, 'resetCoverPhoto'])->name('users.reset-cover-photo');
        Route::post('/users/{user}/update-username', [UserManagementController::class, 'updateUsername'])->name('users.update-username');
        Route::post('/users/{user}/moderate-bio', [UserManagementController::class, 'moderateBio'])->name('users.moderate-bio');
        Route::post('/users/{user}/toggle-verification', [UserManagementController::class, 'toggleVerification'])->name('users.toggle-verification');
        
        // Verification
        Route::get('/verifications', [AdminVerificationController::class, 'index'])->name('verifications.index');
        Route::get('/verifications/{user}', [AdminVerificationController::class, 'show'])->name('verifications.show');
        Route::post('/verifications/{user}/approve', [AdminVerificationController::class, 'approve'])->name('verifications.approve');
        Route::post('/verifications/{user}/approve', [AdminVerificationController::class, 'approve'])->name('verifications.approve');
        Route::post('/verifications/{user}/reject', [AdminVerificationController::class, 'reject'])->name('verifications.reject');
        
        // Analyst Applications
        Route::get('/analyst-applications', [\App\Http\Controllers\Admin\AnalystApplicationController::class, 'index'])->name('analyst-applications.index');
        Route::get('/analyst-applications/{application}', [\App\Http\Controllers\Admin\AnalystApplicationController::class, 'show'])->name('analyst-applications.show');
        Route::post('/analyst-applications/{application}/approve', [\App\Http\Controllers\Admin\AnalystApplicationController::class, 'approve'])->name('analyst-applications.approve');
        Route::post('/analyst-applications/{application}/reject', [\App\Http\Controllers\Admin\AnalystApplicationController::class, 'reject'])->name('analyst-applications.reject');
        
        // Subscriptions
        Route::get('/subscriptions', [\App\Http\Controllers\Admin\SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::get('/subscriptions/{id}', [\App\Http\Controllers\Admin\SubscriptionController::class, 'show'])->name('subscriptions.show');
        Route::post('/subscriptions/{id}/cancel', [\App\Http\Controllers\Admin\SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
        
        // Disputes
        Route::get('/disputes', [\App\Http\Controllers\Admin\DisputeController::class, 'index'])->name('disputes.index');
        Route::get('/disputes/{id}', [\App\Http\Controllers\Admin\DisputeController::class, 'show'])->name('disputes.show');
        Route::post('/disputes/{id}/resolve', [\App\Http\Controllers\Admin\DisputeController::class, 'resolve'])->name('disputes.resolve');
        
        // Analyst Assignments
        Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
        Route::post('/assignments/assign', [AssignmentController::class, 'assign'])->name('assignments.assign');
        Route::put('/assignments/{assignment}', [AssignmentController::class, 'reassign'])->name('assignments.reassign');
        Route::delete('/assignments/{assignment}', [AssignmentController::class, 'remove'])->name('assignments.remove');
        
        // Analyst Assignment Requests
        Route::get('/assignments/requests', [AdminAnalystRequestController::class, 'index'])->name('assignments.requests.index');
        Route::put('/assignments/requests/{analystRequest}', [AdminAnalystRequestController::class, 'update'])->name('assignments.requests.update');
        
        // Analytics Oversight
        Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/analytics/trades', [AdminAnalyticsController::class, 'allTrades'])->name('analytics.trades');
        Route::get('/analytics/traders/{trader}', [AdminAnalyticsController::class, 'traderAnalytics'])->name('analytics.trader');
        
        // Activity Logs
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('/activity-logs/export', [ActivityLogController::class, 'export'])->name('activity-logs.export');
        
        // Backup Management
        Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups/create', [BackupController::class, 'create'])->name('backups.create');
        Route::get('/backups/{filename}/download', [BackupController::class, 'download'])->name('backups.download');
        Route::post('/backups/{filename}/restore', [BackupController::class, 'restore'])->name('backups.restore');
        Route::delete('/backups/{filename}', [BackupController::class, 'destroy'])->name('backups.destroy');
        
        // System Settings
        Route::get('/settings', [SystemSettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SystemSettingsController::class, 'update'])->name('settings.update');
        
        // Analyst Reviews
        Route::post('/analysts/{analyst}/reviews', [AnalystReviewController::class, 'store'])->name('analysts.review.store');
        Route::delete('/reviews/{review}', [AnalystReviewController::class, 'destroy'])->name('analysts.review.destroy');
    });

    // Analyst Routes (requires verification)
    Route::middleware(['role:analyst', EnsureVerified::class])->prefix('analyst')->name('analyst.')->group(function () {
        Route::get('/dashboard', [AnalystDashboardController::class, 'index'])->name('dashboard');
        Route::get('/traders/{trader}', [AnalystDashboardController::class, 'traderProfile'])->name('trader.profile');
        Route::post('/traders/{trader}/simulate', [AnalystDashboardController::class, 'simulate'])->name('trader.simulate');
        
        // Governance (Phase 6)
        Route::post('/traders/{trader}/focus', [AnalystDashboardController::class, 'updateFocus'])->name('trader.update-focus');
        Route::post('/traders/{trader}/rules', [AnalystDashboardController::class, 'storeRule'])->name('trader.rules.store');
        Route::delete('/rules/{rule}', [AnalystDashboardController::class, 'deleteRule'])->name('rules.destroy');
        
        // Feedback Management (Using POST for creation because of complex form, but conventionally GET create)
        Route::get('/feedback/create/{trader}/{trade?}', [AnalystFeedbackController::class, 'create'])->name('feedback.create');
        Route::post('/feedback', [AnalystFeedbackController::class, 'store'])->name('feedback.store');
        
        // AI Draft Generation
        Route::post('/feedback/{trader}/generate-draft', [AnalystFeedbackController::class, 'generateDraft'])->name('feedback.generate-draft');
        
        Route::get('/feedback/{feedback}/edit', [AnalystFeedbackController::class, 'edit'])->name('feedback.edit');
        Route::put('/feedback/{feedback}', [AnalystFeedbackController::class, 'update'])->name('feedback.update');
        Route::delete('/feedback/{feedback}', [AnalystFeedbackController::class, 'destroy'])->name('feedback.destroy');
        
        // Feedback Templates
        Route::resource('templates', FeedbackTemplateController::class);
    });

    // Trader Routes (requires verification)
    Route::middleware(['role:trader', EnsureVerified::class])->prefix('trader')->name('trader.')->group(function () {
        Route::get('/dashboard', [TraderDashboardController::class, 'index'])->name('dashboard');
        
        // Trade Management
        Route::resource('trades', TradeController::class);
        
        // Strategies
        Route::resource('strategies', StrategyController::class);
        
        // Trade Accounts
        Route::resource('accounts', TradeAccountController::class);
        Route::post('/accounts/{account}/transaction', [TradeAccountController::class, 'addTransaction'])
            ->name('accounts.transaction');
        
        // Analytics
        Route::get('/analytics', [TraderAnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/analytics/review', [TraderAnalyticsController::class, 'review'])->name('analytics.review');
        Route::get('/analytics/pair/{pair}', [TraderAnalyticsController::class, 'pairAnalysis'])->name('analytics.pair');
        
        // Feedback
        Route::get('/feedback', [TraderFeedbackController::class, 'index'])->name('feedback.index');
        Route::get('/feedback/{feedback}', [TraderFeedbackController::class, 'show'])->name('feedback.show');
        
        // Trading Tools
        Route::get('/tools', function () {
            return view('trader.tools.index');
        })->name('tools.index');
        
        // Achievements
        Route::get('/achievements', [AchievementController::class, 'index'])->name('achievements.index');
        
        // Leaderboard
        Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');

        // Subscription Management
        Route::get('/subscriptions', [\App\Http\Controllers\Trader\SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::get('/subscriptions/{id}', [\App\Http\Controllers\Trader\SubscriptionController::class, 'show'])->name('subscriptions.show');
        Route::post('/subscriptions/{id}/cancel', [\App\Http\Controllers\Trader\SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
        
        // Dispute Management
        Route::get('/subscriptions/{id}/dispute/create', [\App\Http\Controllers\Trader\DisputeController::class, 'create'])->name('disputes.create');
        Route::post('/subscriptions/{id}/dispute', [\App\Http\Controllers\Trader\DisputeController::class, 'store'])->name('disputes.store');
        Route::get('/disputes', [\App\Http\Controllers\Trader\DisputeController::class, 'index'])->name('disputes.index');
        Route::get('/disputes/{id}', [\App\Http\Controllers\Trader\DisputeController::class, 'show'])->name('disputes.show');
    });

    // Phase 1: Subscriptions (Available to all authenticated users)
    Route::get('/subscribe/{analyst}', [SubscriptionController::class, 'create'])->name('subscription.create');
    Route::post('/subscribe/{analyst}', [SubscriptionController::class, 'checkout'])->name('subscription.checkout');
    Route::get('/subscription/success', [SubscriptionController::class, 'success'])->name('subscription.success');
    Route::get('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    Route::delete('/subscription/{subscription}', [SubscriptionController::class, 'destroy'])->name('subscription.destroy');
    
    // Phase 1: Reviews (Available to all authenticated users)
    Route::post('/analysts/{analyst}/review', [AnalystReviewController::class, 'store'])->name('analysts.review.store');
    Route::delete('/reviews/{review}', [AnalystReviewController::class, 'destroy'])->name('reviews.destroy');

    // Analyst Routes
    Route::prefix('analyst')->middleware(['role:analyst'])->group(function () {
        // ... existing analyst routes ...
        
        // Phase 1: Revenue & Payouts
        Route::get('/revenue', [AnalystDashboardController::class, 'revenue'])->name('analyst.revenue');
        Route::get('/payouts', [AnalystPayoutController::class, 'index'])->name('analyst.payouts.index');
        Route::post('/payouts/request', [AnalystPayoutController::class, 'request'])->name('analyst.payouts.request');
        
        // Phase 1: Profile Management
        Route::get('/profile/edit', [AnalystProfileController::class, 'edit'])->name('analyst.profile.edit');
        Route::put('/analyst/profile', [AnalystProfileController::class, 'update'])->name('analyst.profile.update');
    Route::put('/analyst/profile/plans', [AnalystProfileController::class, 'updatePlans'])->name('analyst.profile.plans.update');
    });

    // Admin Routes (Review Moderation)
    Route::prefix('admin')->middleware(['role:admin'])->group(function () {
        // Phase 1: Review Moderation
        Route::post('/reviews/{review}/approve', [AnalystReviewController::class, 'approve'])->name('admin.reviews.approve');
    });

    // Phase 2: Direct Messaging
    Route::get('/conversations', [\App\Http\Controllers\ConversationController::class, 'index'])->name('conversations.index');
    Route::get('/conversations/{conversation}', [\App\Http\Controllers\ConversationController::class, 'show'])->name('conversations.show');
    Route::post('/conversations', [\App\Http\Controllers\ConversationController::class, 'store'])->name('conversations.store');
    
    Route::post('/conversations/{conversation}/messages', [\App\Http\Controllers\MessageController::class, 'store'])->name('messages.store');
    Route::get('/conversations/{conversation}/messages/poll', [\App\Http\Controllers\MessageController::class, 'poll'])->name('messages.poll');

});



// Welcome page (redirect to login)
Route::get('/', function () {
    return redirect()->route('login');
});

// DEBUG ROUTE - Check photo values
Route::get('/debug-photo/{userId}', function($userId) {
    $user = \App\Models\User::find($userId);
    
    if (!$user) {
        return "User not found";
    }
    
    return response()->json([
        'id' => $user->id,
        'name' => $user->name,
        'username' => $user->username,
        'profile_photo_db' => $user->profile_photo,
        'cover_photo_db' => $user->cover_photo,
        'profile_photo_url' => $user->getProfilePhotoUrl(),
        'cover_photo_url' => $user->getCoverPhotoUrl(),
        'files_in_storage' => \Illuminate\Support\Facades\Storage::disk('public')->files('profiles'),
    ]);
});


