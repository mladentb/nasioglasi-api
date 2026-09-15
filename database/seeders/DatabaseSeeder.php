<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@nasioglasi.net',
            'phone' => '+381600000000',
            'password' => Hash::make(env('SEED_ADMIN_PASSWORD', Str::random(32))),
            'country' => 'RS',
            'role' => 'super_admin',
            'is_admin' => true,
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->call(CategorySeeder::class);
        $this->call(DemoListingsSeeder::class);
    }
}
