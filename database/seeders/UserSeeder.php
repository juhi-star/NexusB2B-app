<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        $accounts = [
            ['name' => 'Morgan Reed', 'email' => 'manager@nexusb2b.test', 'role' => UserRole::CreditManager],
            ['name' => 'Jordan Diaz', 'email' => 'rep@nexusb2b.test', 'role' => UserRole::SalesRepresentative],
            ['name' => 'Sam Rivera', 'email' => 'rep2@nexusb2b.test', 'role' => UserRole::SalesRepresentative],
        ];

        foreach ($accounts as $account) {
            User::query()->updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => $password,
                    'role' => $account['role']->value,
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
