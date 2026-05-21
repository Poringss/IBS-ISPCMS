<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Faker\Factory as Faker;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Wipe old demo rows if you want a clean slate (optional)
        // DB::table('leads')->truncate();
        // DB::table('projects')->truncate();
        // DB::table('tasks')->truncate();

        // ---------- helper to pick a date in the past N days ----------
        $randPast = function (int $days) {
            $d = Carbon::now()->subDays(random_int(0, $days));
            // add some hour/min randomness so daily buckets look real
            $d->setTime(random_int(8, 20), random_int(0, 59));
            return $d;
        };

        // ---------- Leads (spread across last 365 days) ----------
        $stages  = ['Prospect','Contacted','Proposal','Closed'];
        $statuses = ['open','won','lost'];
        $leadRows = [];

        for ($i = 0; $i < 600; $i++) {
            $created = $randPast(365);
            $status  = $faker->randomElement($statuses);
            // small tilt so “won” isn’t too rare
            if ($faker->boolean(20)) $status = 'won';

            $leadRows[] = [
                'name'       => $faker->company(),
                'stage'      => $faker->randomElement($stages),
                'status'     => $status,
                'value'      => $faker->numberBetween(1000, 250000),
                'created_at' => $created,
                'updated_at' => $created->copy()->addDays(random_int(0, 14)),
            ];
        }

        // ---------- Projects (good/bad outcomes) ----------
        $projectRows = [];
        for ($i = 0; $i < 220; $i++) {
            $created = $randPast(365);
            $result  = $faker->boolean(65) ? 'good' : 'bad'; // ~65/35 split

            $projectRows[] = [
                'name'       => 'project_' . Str::slug($faker->bs()) . '_' . $faker->numberBetween(1, 20),
                'result'     => $result, // your code uses 'good' | 'bad'
                'created_at' => $created,
                'updated_at' => $created->copy()->addDays(random_int(0, 10)),
            ];
        }

        // ---------- Tasks (completed -> updated_at as completion proxy) ----------
        $taskTitles = [
            'draft proposal','client follow-up','requirements review','spec update',
            'demo prep','meeting arrangement plans','internal QA','handoff note',
        ];
        $taskRows = [];
        for ($i = 0; $i < 500; $i++) {
            $created  = $randPast(365);
            $completedAt = $created->copy()->addDays(random_int(0, 14));

            $taskRows[] = [
                'title'      => $faker->randomElement($taskTitles),
                'status'     => 'completed',
                'due_date'   => $created->copy()->addDays(random_int(1, 21)),
                'created_at' => $created,
                'updated_at' => $completedAt, // used by your charts as “completion time”
            ];
        }

        // ---------- bulk insert (SQLite friendly) ----------
        foreach (array_chunk($leadRows, 200) as $chunk)    DB::table('leads')->insert($chunk);
        foreach (array_chunk($projectRows, 200) as $chunk) DB::table('projects')->insert($chunk);
        foreach (array_chunk($taskRows, 200) as $chunk)    DB::table('tasks')->insert($chunk);
    }
}
