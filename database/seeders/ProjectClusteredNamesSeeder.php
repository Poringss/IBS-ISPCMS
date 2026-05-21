<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProjectClusteredNamesSeeder extends Seeder
{
    public function run(): void
    {
        $clientIds = DB::table('clients')->pluck('id')->all();
        if (empty($clientIds)) return;

        // Base names you want to repeat
        $names = [
            'Website Revamp',
            'Mobile App',
            'CRM Integration',
            'Onboarding Flow',
            'SEO Sprint',
            'Bug Bash',
            'Landing Page',
            'Data Migration',
        ];

        $count    = 180;
        $daysBack = 120;

        for ($i = 0; $i < $count; $i++) {
            $created = Carbon::now()
                ->subDays(rand(0, $daysBack))
                ->setTime(rand(9, 19), rand(0, 59));

            $name = $names[array_rand($names)];
            if (rand(1,100) <= 30) {
                $name .= ' v'.rand(1,3);
            }

            DB::table('projects')->insert([
                'client_id'  => $clientIds[array_rand($clientIds)],
                'name'       => $name,
                'result'     => rand(1,100) <= 65 ? 'good' : 'bad',
                'created_at' => $created,
                'updated_at' => $created->copy()->addDays(rand(0,10)),
            ]);
        }

        DB::table('projects')->whereNull('result')->update(['result' => 'good']);
    }
}
