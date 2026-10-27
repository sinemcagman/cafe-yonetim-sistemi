<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Her rol için örnek bir çalışan hesabı oluşturur. Varsayılan şifre: "password".
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Selin Aydın', 'email' => 'yonetici@kafe.test', 'role' => UserRole::Yonetici],
            ['name' => 'Ayşe Demir', 'email' => 'garson@kafe.test', 'role' => UserRole::Garson],
            ['name' => 'Mehmet Kaya', 'email' => 'kasiyer@kafe.test', 'role' => UserRole::Kasiyer],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user + ['password' => 'password', 'is_active' => true],
            );
        }
    }
}
