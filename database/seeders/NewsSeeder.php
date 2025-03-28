<?php

namespace Database\Seeders;
use App\Models\News;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        News::create([
            'title' => 'Test News',
            'content' => 'This is a test news content.',
            'link' => 'https://example.com',
            'image' => 'test.jpg',
        ]);
    }
}
