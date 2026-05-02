<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class ListUsersSeeder extends Seeder
{
    public function run(): void
    {
        foreach(User::all() as $u) {
            $this->command->info($u->id . ": " . $u->nombre . " (" . $u->username . ") - Rol: " . $u->rol);
        }
    }
}
