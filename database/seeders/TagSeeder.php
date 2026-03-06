<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['name' => 'New Arrival', 'color' => '#3B82F6'],
            ['name' => 'Best Seller', 'color' => '#EF4444'],
            ['name' => 'Limited Edition', 'color' => '#8B5CF6'],
        ];

        foreach ($tags as $tag) {
            Tag::create([
                'name' => $tag['name'],
                'slug' => Str::slug($tag['name']),
                'color_code' => $tag['color'],
            ]);
        }
    }
}