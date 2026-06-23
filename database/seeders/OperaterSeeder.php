<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class OperaterSeeder extends Seeder
{
    public function run(): void
    {
        // Operater korisnik
        $operater = User::create([
            'name' => 'Operater',
            'email' => 'operater@uzrj.rs',
            'password' => bcrypt('operater123'),
        ]);
        $operater->assignRole('operater');
    }
}
