<?php

use Illuminate\Database\Seeder;

class PlansTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        app('rinvex.subscriptions.plan')->create([
          'name' => 'Month',
          'description' => 'Monthly plan',
          'price' => 19,
          'signup_fee' => 0,
          'invoice_period' => 1,
          'invoice_interval' => 'month',
          'trial_period' => 1,
          'trial_interval' => 'day',
          'sort_order' => 1,
          'currency' => 'CNY',
      ]);
        app('rinvex.subscriptions.plan')->create([
          'name' => 'Quarter',
          'description' => 'Quarterly plan',
          'price' => 29,
          'signup_fee' => 0,
          'invoice_period' => 3,
          'invoice_interval' => 'month',
          'trial_period' => 1,
          'trial_interval' => 'day',
          'sort_order' => 1,
          'currency' => 'CNY',
      ]);
        app('rinvex.subscriptions.plan')->create([
          'name' => 'Year',
          'description' => 'Yearly plan',
          'price' => 99,
          'signup_fee' => 0,
          'invoice_period' => 12,
          'invoice_interval' => 'month',
          'trial_period' => 1,
          'trial_interval' => 'day',
          'sort_order' => 1,
          'currency' => 'CNY',
      ]);
    }
}
