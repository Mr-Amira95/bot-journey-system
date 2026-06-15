<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        $users = [
            // Team members
            ['name' => 'Sarah Chen',        'email' => 'sarah.chen@botjourney.ai',        'role' => 'manager',  'last_login' => 0],
            ['name' => 'James Wilson',      'email' => 'james.wilson@botjourney.ai',      'role' => 'manager',  'last_login' => 1],
            ['name' => 'Emma Rodriguez',    'email' => 'emma.rodriguez@botjourney.ai',    'role' => 'employee', 'last_login' => 0],
            ['name' => 'Liam Thompson',     'email' => 'liam.thompson@botjourney.ai',     'role' => 'employee', 'last_login' => 2],
            ['name' => 'Olivia Davis',      'email' => 'olivia.davis@botjourney.ai',      'role' => 'employee', 'last_login' => 1],
            ['name' => 'Noah Martinez',     'email' => 'noah.martinez@botjourney.ai',     'role' => 'employee', 'last_login' => 0],
            ['name' => 'Ava Johnson',       'email' => 'ava.johnson@botjourney.ai',       'role' => 'employee', 'last_login' => 3],
            ['name' => 'William Brown',     'email' => 'william.brown@botjourney.ai',     'role' => 'employee', 'last_login' => 1],
            ['name' => 'Isabella Garcia',   'email' => 'isabella.garcia@botjourney.ai',   'role' => 'manager',  'last_login' => 2],
            ['name' => 'Lucas Anderson',    'email' => 'lucas.anderson@botjourney.ai',    'role' => 'manager',  'last_login' => 0],
            ['name' => 'Mia Taylor',        'email' => 'mia.taylor@botjourney.ai',        'role' => 'employee', 'last_login' => 1],
            ['name' => 'Ethan Lee',         'email' => 'ethan.lee@botjourney.ai',         'role' => 'manager',  'last_login' => 4],
            ['name' => 'Charlotte Clark',   'email' => 'charlotte.clark@botjourney.ai',   'role' => 'manager',  'last_login' => 1],
            ['name' => 'Alexander Wright',  'email' => 'alexander.wright@botjourney.ai',  'role' => 'employee', 'last_login' => 2],
            ['name' => 'Amelia Lewis',      'email' => 'amelia.lewis@botjourney.ai',      'role' => 'employee', 'last_login' => 0],
            // Client users
            ['name' => 'Marcus Reed',       'email' => 'marcus.reed@nexusai.com',         'role' => 'client',   'last_login' => 5],
            ['name' => 'Priya Sharma',      'email' => 'priya.sharma@healthflow.com',     'role' => 'client',   'last_login' => 3],
            ['name' => 'Derek Walsh',       'email' => 'derek.walsh@retailedge.com',      'role' => 'client',   'last_login' => 7],
            ['name' => 'Yuki Tanaka',       'email' => 'yuki.tanaka@smartlogistics.com',  'role' => 'client',   'last_login' => 14],
            ['name' => 'Fatima Al-Hassan',  'email' => 'fatima.alhassan@futurelearn.edu', 'role' => 'client',   'last_login' => 2],
        ];

        $roleCache = [];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'              => $data['name'],
                    'password'          => $password,
                    'status'            => UserStatus::Active,
                    'email_verified_at' => now(),
                    'last_login_at'     => now()->subDays($data['last_login']),
                ]
            );

            if (!isset($roleCache[$data['role']])) {
                $roleCache[$data['role']] = Role::where('slug', $data['role'])->first();
            }

            $role = $roleCache[$data['role']];
            if ($role) {
                $user->roles()->syncWithoutDetaching([$role->id]);
            }
        }

        $this->command->info('Users seeded: ' . count($users) . ' (all passwords: "password")');
    }
}
