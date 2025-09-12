<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolClass;
use Illuminate\Support\Facades\DB;

class ClassesTableSeeder extends Seeder
{
    public function run(): void
    {
        $classes = [
            // Kelas 10
            ['name' => '10 AK 1', 'level' => '10', 'program' => 'AK', 'number' => 1, 'is_active' => true],
            ['name' => '10 AK 2', 'level' => '10', 'program' => 'AK', 'number' => 2, 'is_active' => true],
            ['name' => '10 TKJ 1', 'level' => '10', 'program' => 'TKJ', 'number' => 1, 'is_active' => true],
            ['name' => '10 TKJ 2', 'level' => '10', 'program' => 'TKJ', 'number' => 2, 'is_active' => true],
            ['name' => '10 TKJ 3', 'level' => '10', 'program' => 'TKJ', 'number' => 3, 'is_active' => true],
            ['name' => '10 BID', 'level' => '10', 'program' => 'BID', 'number' => 1, 'is_active' => true],
            
            // Kelas 11
            ['name' => '11 AK 1', 'level' => '11', 'program' => 'AK', 'number' => 1, 'is_active' => true],
            ['name' => '11 AK 2', 'level' => '11', 'program' => 'AK', 'number' => 2, 'is_active' => true],
            ['name' => '11 TKJ 1', 'level' => '11', 'program' => 'TKJ', 'number' => 1, 'is_active' => true],
            ['name' => '11 TKJ 2', 'level' => '11', 'program' => 'TKJ', 'number' => 2, 'is_active' => true],
            ['name' => '11 TKJ 3', 'level' => '11', 'program' => 'TKJ', 'number' => 3, 'is_active' => true],
            ['name' => '11 BID', 'level' => '11', 'program' => 'BID', 'number' => 1, 'is_active' => true],
            
            // Kelas 12
            ['name' => '12 AK 1', 'level' => '12', 'program' => 'AK', 'number' => 1, 'is_active' => true],
            ['name' => '12 AK 2', 'level' => '12', 'program' => 'AK', 'number' => 2, 'is_active' => true],
            ['name' => '12 TKJ 1', 'level' => '12', 'program' => 'TKJ', 'number' => 1, 'is_active' => true],
            ['name' => '12 TKJ 2', 'level' => '12', 'program' => 'TKJ', 'number' => 2, 'is_active' => true],
            ['name' => '12 TKJ 3', 'level' => '12', 'program' => 'TKJ', 'number' => 3, 'is_active' => true],
            ['name' => '12 BID', 'level' => '12', 'program' => 'BID', 'number' => 1, 'is_active' => true],
        ];

        foreach ($classes as $class) {
            SchoolClass::create([
                'name' => $class['name'],
                'level' => $class['level'],
                'program' => $class['program'],
                'number' => $class['number'],
                'is_active' => $class['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Seeder kelas berhasil ditambahkan: ' . count($classes) . ' kelas');
    }
}