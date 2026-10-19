<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'charles.andres.sanchez@gmail.com',
            'password' => \Illuminate\Support\Facades\Hash::make('chaly2847'),
        ]);

        $this->call([
            CategoriaSeeder::class,
        ]);
    }
}
