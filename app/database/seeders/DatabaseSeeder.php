<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            DemoSeeder::class,
            ProgramSeeder::class,
            CampaignSeeder::class,
            ArticleSeeder::class,
            AlbumSeeder::class,
            MemberSeeder::class,
        ]);
    }
}
