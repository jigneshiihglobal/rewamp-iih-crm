<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MarketingExpenseType;

class MarketingExpenseTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = now();
        $marketing_expense_type = array(
            array('title' => 'Google Ads','created_at' => $now,'updated_at' => $now),
            array('title' => 'Clutch','created_at' => $now,'updated_at' => $now),
            array('title' => 'Content writer','created_at' => $now,'updated_at' => $now),
            array('title' => 'Designs','created_at' => $now,'updated_at' => $now),
            array('title' => 'Social media planner','created_at' => $now,'updated_at' => $now),
        );

        MarketingExpenseType::insert($marketing_expense_type);
    }
}
