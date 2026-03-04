<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Participant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ParticipantSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all events
        $events = Event::all();

        // Sample participant data
        $participantData = [
            ['name' => 'John Smith', 'email' => 'john.smith@example.com'],
            ['name' => 'Jane Doe', 'email' => 'jane.doe@example.com'],
            ['name' => 'Mike Johnson', 'email' => 'mike.johnson@example.com'],
            ['name' => 'Sarah Williams', 'email' => 'sarah.williams@example.com'],
            ['name' => 'David Brown', 'email' => 'david.brown@example.com'],
            ['name' => 'Emma Davis', 'email' => 'emma.davis@example.com'],
            ['name' => 'Robert Miller', 'email' => 'robert.miller@example.com'],
            ['name' => 'Lisa Anderson', 'email' => 'lisa.anderson@example.com'],
            ['name' => 'James Taylor', 'email' => 'james.taylor@example.com'],
            ['name' => 'Mary Thomas', 'email' => 'mary.thomas@example.com'],
            ['name' => 'Charles Martin', 'email' => 'charles.martin@example.com'],
            ['name' => 'Patricia Jackson', 'email' => 'patricia.jackson@example.com'],
        ];

        // Add 8-12 participants to each event
        foreach ($events as $event) {
            $participantCount = rand(8, min(12, $event->capacity));
            $selectedParticipants = array_slice($participantData, 0, $participantCount);

            foreach ($selectedParticipants as $participant) {
                Participant::create([
                    'event_id' => $event->id,
                    'name' => $participant['name'],
                    'email' => $participant['email'],
                ]);
            }

            // Shuffle for next event
            shuffle($participantData);
        }
    }
}
