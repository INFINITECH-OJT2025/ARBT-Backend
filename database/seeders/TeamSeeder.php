<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;

class TeamSeeder extends Seeder
{
    public function run()
    {
        Team::create([
            'name' => 'Della Clover',
            'role' => 'UX Designer',
            'description' => 'Sed placerat luctus mi, mollis mattis nisl accumsan mollis.',
            'image_url' => 'https://example.com/images/della.jpg', // Replace with actual URL
            'status' => 'Active'
        ]);

        Team::create([
            'name' => 'Gian Banks',
            'role' => 'Web Developer',
            'description' => 'Sed placerat luctus mi, mollis mattis nisl accumsan mollis.',
            'image_url' => 'https://example.com/images/gian.jpg', // Replace with actual URL
            'status' => 'Active'
        ]);

        Team::create([
            'name' => 'Stella Zoe',
            'role' => 'AI Expert',
            'description' => 'Sed placerat luctus mi, mollis mattis nisl accumsan mollis.',
            'image_url' => 'https://example.com/images/stella.jpg', // Replace with actual URL
            'status' => 'Active'
        ]);
    }
}