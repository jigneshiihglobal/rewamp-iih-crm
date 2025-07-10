<?php

namespace Database\Seeders;

use App\Models\ExpenseSubType;
use App\Models\ExpenseType;
use Illuminate\Database\Seeder;

class ExpenseNameChange extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*Web hosting update */
        ExpenseType::where('title','Hosting')->update(['title'=>'Web Hosting']);

        /* Domain name add */
        ExpenseType::create([
            'title'=>  'Domain',
        ]);

        /* ExpenseSubTypes add */
        $expense_type = ExpenseType::select('id')->where('title','Domain')->first();
        $sub_types = ['Godaddy'];
        foreach ($sub_types as $sub_type){
            $ExpenseSubType =new ExpenseSubType();
            $ExpenseSubType->expense_type_id = $expense_type->id;
            $ExpenseSubType->title = $sub_type;
            $ExpenseSubType->save();
        }
    }
}
