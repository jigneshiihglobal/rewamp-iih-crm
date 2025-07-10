<?php

namespace App\Services;

use App\Models\LeadStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeadStatusService
{
    /**
     * This function stores the given lead status in database.
     *
     * By default the new lead status will have the lowest priority,
     * and if the priority gets to 0 or negative number than
     * priority of all status are increased, and new status is given
     * the lowest priority i.e. 1.
     *
     * @param array $data Array of lead status fields and values.
     * @return \App\Models\LeadStatus
     * @author Krunal Shrimali
     */
    public function store(array $data): LeadStatus
    {
        $data['priority'] = ((int) LeadStatus::min('priority')) - 1;

        $lead_status = DB::transaction(function () use ($data) {

            if ($data['priority'] <= 0) {

                $lead_statuses = LeadStatus::query()
                    ->orderByDesc('priority')
                    ->get([
                        'id',
                        'priority',
                        'title'
                    ]);

                $updated_lead_statuses = array_map(
                    function ($lead_status) {
                        return [
                            'id' => $lead_status['id'],
                            'title' => $lead_status['title'],
                            'priority' => $lead_status['priority'] + 1
                        ];
                    },
                    $lead_statuses->toArray()
                );

                LeadStatus::upsert($updated_lead_statuses, ['id'], ['priority']);
                $data['priority'] = $data['priority'] + 1;
            }

            $lead_status = LeadStatus::create($data);

            return $lead_status;
        });

        return $lead_status;
    }

    /**
     * This function updates the given lead status in database.
     *
     * @param \App\Models\LeadStatus $lead_status The lead status
     * instance to be updated
     * @param array $data Array of lead status fields and values
     * to be updated.
     * @return \App\Models\LeadStatus updated lead status
     * @author Krunal Shrimali
     */
    public function update(LeadStatus $lead_status, array $data): LeadStatus
    {
        $lead_status->update($data);

        return $lead_status;
    }

    /**
     * This function softly delete the given lead status in database.
     *
     * @param \App\Models\LeadStatus $lead_status The lead status
     * instance to be soft deleted
     * @return boolean did soft delete or not
     * @author Krunal Shrimali
     */
    public function delete(LeadStatus $lead_status): bool
    {

        $lead_status->loadCount(['leads']);
        abort_if($lead_status->leads_count, 400, 'Unable to delete, status has leads!');

        return $lead_status->delete();
    }

    /**
     * This function restores the given soft deleted lead status
     *
     * @param \App\Models\LeadStatus $lead_status The soft deleted
     * lead status
     * @return boolean did restore or not
     * @author Krunal Shrimali
     */
    public function restore(LeadStatus $lead_status): bool
    {
        return $lead_status->restore();
    }

    /**
     * This function permanently delete the given lead status
     * permanently from database.
     *
     * @param \App\Models\LeadStatus $lead_status The lead status
     * instance to be permanently deleted
     * @return boolean did permanently delete or not
     * @author Krunal Shrimali
     */
    public function forceDelete(LeadStatus $lead_status): bool
    {
        $lead_status->loadCount(['leads']);
        abort_if(
            $lead_status->leads_count,
            400,
            'Unable to delete, status has leads!'
        );

        return $lead_status->forceDelete();
    }
}
