<?php

namespace Database\Seeders;

use App\Models\LeadSource;
use Illuminate\Database\Seeder;

class RenameWebInquiryLeadSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $leadSource = LeadSource::find(5); // "Web inquiry"
        if($leadSource && $leadSource->title == "Web inquiry") {
            $leadSource->update(['title' => 'SEO / Web inquiry']);
        }
    }
}
