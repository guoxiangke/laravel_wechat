<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
         $this->call(WechatAccountsTableSeeder::class);
         $this->call(LymetasTableSeeder::class);
         $this->call(CategoriesTableSeeder::class);
         $this->call(AutoRepliesTableSeeder::class);
         $this->call(RolesAndPermissionsSeeder::class);
         $this->call(PlansTableSeeder::class);
         $this->call(UsersTableSeeder::class);
    }
}
