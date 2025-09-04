<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User1',
            'name2' => 'Juan',
            'surname' => 'Munoz',
            'surname2' => 'Devia',
            'rol' => 'Administrador',
            'email' => 'juan@example.com',
            'password' => Hash::make('12345678')
        ]);

        User::factory()->create([
            'name' => 'Test User2',
            'name2' => 'prueba',
            'surname' => 'pprueba',
            'surname2' => 'ppruebados',
            'rol' => 'Panadero',
            'email' => 'pp@example.com',
            'password' => Hash::make('12345678')
        ]);

        User::factory()->create([
            'name' => 'Test User3',
            'name2' => 'prueba',
            'surname' => 'pruebatres',
            'surname2' => 'pruebatres',
            'rol' => 'Cajero',
            'email' => 'cc@example.com',
            'password' => Hash::make('12345678')
        ]);
    }
}
