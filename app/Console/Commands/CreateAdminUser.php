<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:admin 
                            {--name= : The full name of the admin user} 
                            {--email= : The email address of the admin user} 
                            {--password= : The account password (minimum 8 characters)}';

    /**
     * Command aliases.
     *
     * @var array
     */
    protected $aliases = ['admin:create'];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new admin user or promote an existing account to Administrator';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=========================================');
        $this->info(' TradeCompass :: Admin Account Manager   ');
        $this->info('=========================================');

        // 1. Gather & Validate Email
        $email = $this->option('email');
        while (empty($email)) {
            $input = $this->ask('Enter user email address');
            $validator = Validator::make(['email' => $input], [
                'email' => ['required', 'string', 'email:rfc,dns'],
            ]);

            // Fallback to simple email validation if DNS check fails in local env
            if ($validator->fails()) {
                $fallbackValidator = Validator::make(['email' => $input], [
                    'email' => ['required', 'string', 'email'],
                ]);

                if ($fallbackValidator->fails()) {
                    $this->error('Invalid email address format. Please try again.');
                    continue;
                }
            }

            $email = strtolower(trim($input));
        }

        // Validate if passed via --email option
        $emailValidator = Validator::make(['email' => $email], [
            'email' => ['required', 'string', 'email'],
        ]);
        if ($emailValidator->fails()) {
            $this->error('The provided --email option is not a valid email address.');
            return 1;
        }

        // 2. Check if user already exists
        $user = User::where('email', $email)->first();

        // Ensure Spatie 'admin' role exists
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        if ($user) {
            $this->warn("User [{$user->name}] ({$user->email}) already exists in the system.");
            
            if ($user->hasRole('admin')) {
                $this->info("This user is already an Administrator.");
                $this->displayUserSummary($user);
                return 0;
            }

            if (!$this->confirm('Promote this user to Admin?', true)) {
                $this->line('Operation aborted. No changes made.');
                return 0;
            }

            // Promote existing user
            $user->email_verified_at = $user->email_verified_at ?? now();
            $user->verification_status = 'verified';
            $user->is_active = true;

            // Ensure username is not empty
            if (empty($user->username)) {
                $user->username = $this->generateUniqueUsername($user->name ?: 'admin');
            }

            // Optional password update if provided
            if ($this->option('password')) {
                $password = $this->option('password');
                if (strlen($password) < 8) {
                    $this->error('Password must be at least 8 characters long.');
                    return 1;
                }
                $user->password = Hash::make($password);
            }

            $user->save();
            $user->assignRole($adminRole);

            $this->newLine();
            $this->info("User [{$user->email}] has been successfully promoted to Admin!");
            $this->displayUserSummary($user->fresh());
            return 0;
        }

        // 3. New User Registration Flow
        $name = $this->option('name');
        while (empty($name)) {
            $input = $this->ask('Enter admin full name');
            if (empty(trim($input))) {
                $this->error('Name cannot be empty.');
                continue;
            }
            $name = trim($input);
        }

        // Password handling
        $password = $this->option('password');
        if (!empty($password)) {
            if (strlen($password) < 8) {
                $this->error('Password must be at least 8 characters long.');
                return 1;
            }
        } else {
            while (true) {
                $password = $this->secret('Enter admin password (min 8 characters)');
                if (strlen($password) < 8) {
                    $this->error('Password must be at least 8 characters long.');
                    continue;
                }

                $confirmPassword = $this->secret('Confirm admin password');
                if ($password !== $confirmPassword) {
                    $this->error('Passwords do not match. Please try again.');
                    continue;
                }

                break;
            }
        }

        // Generate clean unique username
        $username = $this->generateUniqueUsername($name);

        // Create new Admin User
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'username' => $username,
            'password' => Hash::make($password),
            'email_verified_at' => now(), // Admin accounts bypass verification notice
            'verification_status' => 'verified',
            'is_active' => true,
            'timezone' => 'UTC',
            'profile_visibility' => 'public',
        ]);

        $user->assignRole($adminRole);

        $this->newLine();
        $this->info("New Administrator successfully created!");
        $this->displayUserSummary($user->fresh());

        return 0;
    }

    /**
     * Generate a unique username based on the user's name.
     */
    protected function generateUniqueUsername(string $name): string
    {
        $cleanName = preg_replace('/[^a-z0-9_]/', '', strtolower(str_replace(' ', '_', $name)));
        $base = substr($cleanName ?: 'admin', 0, 40);
        $candidate = $base . '_' . substr(md5(uniqid()), 0, 4);

        while (User::where('username', $candidate)->exists()) {
            $candidate = $base . '_' . substr(md5(uniqid()), 0, 6);
        }

        return $candidate;
    }

    /**
     * Display a formatted summary table of the user.
     */
    protected function displayUserSummary(User $user): void
    {
        $this->table(
            ['Attribute', 'Value'],
            [
                ['User ID', $user->id],
                ['Name', $user->name],
                ['Email', $user->email],
                ['Username', $user->username ?: 'N/A'],
                ['Roles', $user->getRoleNames()->implode(', ') ?: 'None'],
                ['Email Verified', $user->email_verified_at ? $user->email_verified_at->toIso8601String() : 'Not Verified'],
                ['Verification Status', $user->verification_status],
                ['Account Status', $user->is_active ? 'Active' : 'Inactive'],
            ]
        );
    }
}
