<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Event::create([
            'title' => 'Laravel Workshop',
            'description' => 'Learn modern Laravel development practices and build scalable applications.',
            'event_date' => now()->addDays(7),
            'category' => 'Technology',
            'capacity' => 50,
        ]);

        Event::create([
            'title' => 'Web Development Bootcamp',
            'description' => 'Comprehensive bootcamp covering HTML, CSS, JavaScript, and backend development.',
            'event_date' => now()->addDays(14),
            'category' => 'Technology',
            'capacity' => 30,
        ]);

        Event::create([
            'title' => 'PHP Best Practices Seminar',
            'description' => 'Deep dive into PHP best practices, design patterns, and performance optimization.',
            'event_date' => now()->addDays(21),
            'category' => 'Technology',
            'capacity' => 40,
        ]);

        Event::create([
            'title' => 'Database Design & Optimization',
            'description' => 'Master database design, optimization techniques, and query performance tuning.',
            'event_date' => now()->addDays(28),
            'category' => 'Technology',
            'capacity' => 25,
        ]);

        Event::create([
            'title' => 'API Development with Laravel',
            'description' => 'Build robust RESTful APIs using Laravel and best practices for API design.',
            'event_date' => now()->addDays(35),
            'category' => 'Technology',
            'capacity' => 45,
        ]);
    }
}
