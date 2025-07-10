<?php

namespace Database\Seeders;

use App\Models\ExpenseType;
use Illuminate\Database\Seeder;

class ExpenseTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $expenseTypes = [
            'Hosting' => [
                "Digital Ocean",
                "AWS",
                "Godaddy",
                "Wp-Engine",
                "Convesio",
            ],
            'SSL' => [
                "Godaddy",
                "SSLs.com",
            ],
            'Email' => [
                "Microsoft 365",
                "Godaddy",
            ],
        ];

        foreach ($expenseTypes as $expenseType => $expenseSubTypes) {
            $type = ExpenseType::create(['title' => $expenseType]);
            foreach ($expenseSubTypes as $expenseSubType) {
                $type->expense_sub_types()->create(['title' => $expenseSubType]);
            }
        }
    }
}
