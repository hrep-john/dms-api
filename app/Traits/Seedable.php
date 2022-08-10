<?php

namespace App\Traits;

use App\Models\Seeder as ModelsSeeder;
use Illuminate\Database\Seeder;

trait Seedable
{
    public function seed($class)
    {
        $last = ModelsSeeder::orderBy('id', 'desc')->count() 
            ? ModelsSeeder::orderBy('id', 'desc')->first()->batch
            : 0;

        ModelsSeeder::insert([
            'seeder' => $class,
            'batch' => $last + 1
        ]);
    }

    public function hasSeeder($name)
    {
        return ModelsSeeder::where('seeder', $name)->count() > 0;
    }
}