<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ('prod' !== app()->environment()) {
            // $this->call(DummyDataSeeder::class);
        }
        // $this->call('UsersTableSeeder');
    }
}
