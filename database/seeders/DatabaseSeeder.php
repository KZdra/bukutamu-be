<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        Schema::disableForeignKeyConstraints();
        DB::table("status_guests")->truncate();
        Schema::enableForeignKeyConstraints();
        $status = [
            ['name' => 'Pending', 'created_at' => now()],
            ['name' => 'Diterima', 'created_at' => now()],
            ['name' => 'Ditolak', 'created_at' => now()],
            ['name' => 'Selesai', 'created_at' => now()],
        ];

        DB::table('status_guests')->insert($status);
        Schema::disableForeignKeyConstraints();
        DB::table('units')->truncate();
        Schema::enableForeignKeyConstraints();

        $units = [
            ['name' => 'Klaim', 'unit_phone' => '08779620392', 'created_at' => now()],
            ['name' => 'Skoring', 'unit_phone' => '08779620392', 'created_at' => now()],

        ];
        DB::table('units')->insert($units);
    }
}
