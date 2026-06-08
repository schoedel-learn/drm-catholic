<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LiturgicalCalendarSeeder extends Seeder
{
    /**
     * Seed the liturgical_calendar_events table from JSON data files.
     */
    public function run(): void
    {
        $dataDir = database_path('seeders/data');
        $files = [
            'liturgical_2026.json',
            'liturgical_2027.json',
        ];

        // Clear existing data
        DB::table('liturgical_calendar_events')->truncate();

        $total = 0;

        foreach ($files as $file) {
            $path = $dataDir . DIRECTORY_SEPARATOR . $file;

            if (!file_exists($path)) {
                $this->command->warn("Skipping {$file}: file not found at {$path}");
                continue;
            }

            $events = json_decode(file_get_contents($path), true);

            if (!is_array($events)) {
                $this->command->error("Invalid JSON in {$file}");
                continue;
            }

            // Batch insert in chunks of 50
            $chunks = array_chunk($events, 50);

            foreach ($chunks as $chunk) {
                $rows = [];
                foreach ($chunk as $event) {
                    $rows[] = [
                        'date' => $event['date'],
                        'title' => $event['title'],
                        'rank' => $event['rank'],
                        'color' => $event['color'],
                        'color_hex' => $event['color_hex'],
                        'season' => $event['season'],
                        'year' => $event['year'],
                        'readings' => json_encode($event['readings']),
                        'notes' => json_encode($event['notes']),
                        'metadata' => json_encode($event['metadata']),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                DB::table('liturgical_calendar_events')->insert($rows);
            }

            $count = count($events);
            $total += $count;
            $this->command->info("Seeded {$count} events from {$file}");
        }

        $this->command->info("Total liturgical events seeded: {$total}");
    }
}
