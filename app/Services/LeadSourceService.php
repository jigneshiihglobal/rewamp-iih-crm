<?php

namespace App\Services;

use App\Models\LeadSource;
use Illuminate\Support\Facades\DB;

class LeadSourceService
{
    /**
     * This function stores the given lead source in database.
     *
     * @param array $data Array of lead source fields and values.
     * @return \App\Models\LeadSource
     * @author Krunal Shrimali
     */
    public function store(array $data): LeadSource
    {
        return  LeadSource::create($data);
    }

    /**
     * This function updates the given lead source in database.
     *
     * @param \App\Models\LeadSource $lead_source The lead source
     * instance to be updated
     * @param array $data Array of lead source fields and values
     * to be updated.
     * @return \App\Models\LeadSource updated lead source
     * @author Krunal Shrimali
     */
    public function update(LeadSource $lead_source, array $data): LeadSource
    {
        $lead_source->update($data);

        return $lead_source;
    }

    /**
     * This function softly delete the given lead source in database.
     *
     * @param \App\Models\LeadSource $lead_source The lead source
     * instance to be soft deleted
     * @return boolean did soft delete or not
     * @author Krunal Shrimali
     */
    public function delete(LeadSource $lead_source): bool
    {

        $lead_source->loadCount(['leads']);
        abort_if($lead_source->leads_count, 400, 'Unable to delete, source has leads!');

        return $lead_source->delete();
    }

    /**
     * This function restores the given soft deleted lead source
     *
     * @param \App\Models\LeadSource $lead_source The soft deleted
     * lead source
     * @return boolean did restore or not
     * @author Krunal Shrimali
     */
    public function restore(LeadSource $lead_source): bool
    {
        return $lead_source->restore();
    }

    /**
     * This function permanently delete the given lead source
     * permanently from database.
     *
     * @param \App\Models\LeadSource $lead_source The lead source
     * instance to be permanently deleted
     * @return boolean did permanently delete or not
     * @author Krunal Shrimali
     */
    public function forceDelete(LeadSource $lead_source): bool
    {
        $lead_source->loadCount(['leads']);
        abort_if(
            $lead_source->leads_count,
            400,
            'Unable to delete, source has leads!'
        );

        return $lead_source->forceDelete();
    }
}
