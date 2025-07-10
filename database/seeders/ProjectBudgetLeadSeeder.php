<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Lead;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectBudgetLeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $leads = Lead::query()
            ->select(DB::raw('DISTINCT(project_budget) AS project_budget'))
            ->whereNotNULL('project_budget')
            ->where('project_budget', '!=', '')
            ->where(function ($query) {
                return $query
                    ->whereNULL('prj_budget')
                    ->orWhere('prj_budget', '');
            })
            ->orderBy('project_budget')
            ->get();

        $currencies = Currency::all(['id', 'symbol']);
        $infoArr = [];
        $updateArr = [];
        $prj_budget_stmt = collect();
        $currency_id_stmt = collect();

        $leads->each(function ($lead, $key) use (&$currencies, &$infoArr, &$updateArr, &$prj_budget_stmt, &$currency_id_stmt) {

            $currency_id = NULL;
            $prj_budget = NULL;

            if (is_numeric($lead->project_budget)) {
                if ($lead->project_budget > 5000) {
                    $prj_budget = '5000';
                } elseif ($lead->project_budget <= 5000 && $lead->project_budget > 2500) {
                    $prj_budget = '2500-5000';
                } elseif ($lead->project_budget <= 2500 && $lead->project_budget > 500) {
                    $prj_budget = '500-2500';
                } elseif ($lead->project_budget <= 500 && $lead->project_budget > 0) {
                    $prj_budget = '0-500';
                }
            } else {
                $gt5000 = [
                    'Below $10K',
                    '$10K - $25K',
                    '$25K - $75K',
                    'USD 20000',
                    '$200K - $500K',
                    'USD 5000-USD 6000',
                    '$75K - $200K',
                    '40k-50k',
                    '£20,000.00 - £50,000.00'
                ];
                $bw5002500 = ['$1000'];
                $bw25005000 = [
                    '$500K and above'

                ];
                $lt500 = [];

                if (in_array($lead->project_budget, $gt5000)) {
                    $prj_budget = '5000';
                } elseif (in_array($lead->project_budget, $bw25005000)) {
                    $prj_budget = '2500-5000';
                } elseif (in_array($lead->project_budget, $bw5002500)) {
                    $prj_budget = '500-2500';
                } elseif (in_array($lead->project_budget, $lt500)) {
                    $prj_budget = '0-500';
                } else {
                    return true;
                }

                if (
                    stripos($lead->project_budget, '$') !== false
                    || stripos($lead->project_budget, 'USD') !== false
                ) {
                    $cur = $currencies->firstWhere('symbol', '$');
                    $currency_id = $cur ? $cur->id : NULL;
                } else if (stripos($lead->project_budget, '£') !== false) {
                    $cur = $currencies->firstWhere('symbol', '£');
                    $currency_id = $cur ? $cur->id : NULL;
                } else if (stripos($lead->project_budget, '€') !== false) {
                    $cur = $currencies->firstWhere('symbol', '€');
                    $currency_id = $cur ? $cur->id : NULL;
                } else {
                    $currency_id = NULL;
                }
            }

            array_push($infoArr, [substr($lead->project_budget, 0, 20), $prj_budget, $currency_id]);
            if ($prj_budget) {
                array_push($updateArr, [$lead->project_budget, $prj_budget, $currency_id]);
                $prj_budget_stmt->push("WHEN leads.project_budget = '{$lead->project_budget}' THEN '{$prj_budget}' ");
                if ($currency_id) {
                    $currency_id_stmt->push("WHEN leads.project_budget = '{$lead->project_budget}' THEN '{$currency_id}' ");
                }
            }
        });

        $this->command->line('Following updated will be applied:');
        $this->command->table(['lead.project_budget', 'lead.prj_budget', 'lead.currency_id'], $updateArr);
        $this->command->newLine();

        if ($prj_budget_stmt->count()) {

            if (!$this->command->confirm('Are you sure you want to make above listed updates?')) {
                $this->command->info("Operation cancelled successfully!");
                return;
            }

            $prj_budget_case_when_stmt = $prj_budget_stmt->implode(' ');
            $currency_id_case_when_stmt = $currency_id_stmt->implode(' ');

            Lead::query()
                ->where('project_budget', '!=', "")
                ->whereNotNULL('project_budget')
                ->where(function ($query) {
                    return $query
                        ->whereNULL('prj_budget')
                        ->orWhere('prj_budget', '');
                })
                ->update([
                    'prj_budget' => DB::raw("(CASE " . $prj_budget_case_when_stmt . " END)"),
                    'currency_id' => DB::raw("(CASE " . $currency_id_case_when_stmt . " END)")
                ]);

            $this->command->info("Updated lead project budgets successfully");
        }
    }
}
