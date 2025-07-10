<?php

namespace App\Exports;

use App\Helpers\DateHelper;
use App\Models\Lead;
use App\Models\LeadStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LeadsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;
    private $createdAtFrom, $createdAtTo;

    public function createdAtFrom($from)
    {
        $this->createdAtFrom = $from;

        return $this;
    }
    public function createdAtTo($to)
    {
        $this->createdAtTo = $to;

        return $this;
    }

    public function leadType($lead_type)
    {
        $this->lead_type = $lead_type;

        return $this;
    }

    public function headings(): array
    {
        return [
            'Source',
            'Name',
            'Email',
            'Phone',
            'Status',
            'Assigned To',
            'Created At',
            'Updated At',
        ];
    }
    public function map($lead): array
    {
        return [
            $lead->lead_source->title ?? '',
            $lead->full_name ?? '',
            $lead->email ?? '',
            $lead->mobile ?? '',
            $lead->lead_status->title ?? '',
            $lead->assignee->full_name ?? '',
            $lead->created_at ? $lead->created_at->format(DateHelper::LEAD_DATE_FORMAT)  : '',
            $lead->updated_at ? $lead->updated_at->format(DateHelper::LEAD_DATE_FORMAT)  : '',
        ];
    }

    public function query()
    {
        $lead_status = LeadStatus::where('title','New')->first();
        $user  = Auth::user();
        return Lead::query()
            ->where('leads.workspace_id', $user->workspace_id)
            ->when($this->lead_type, function ($query) use ($lead_status) {
                if($this->lead_type == 'deleted'){
                    $query->onlyTrashed();
                }elseif($this->lead_type == $lead_status->id){
                    $query->where('leads.lead_status_id', $this->lead_type)->whereNull('leads.assigned_to');
                }else{
                    $query->where('leads.lead_status_id', $this->lead_type);
                }
            })
            ->with([
                "lead_source:id,title",
                "assignee:id,name,first_name,last_name",
                "lead_status:id,title,css_class"
            ])
            ->select(
                "leads.id",
                "leads.lead_status_id",
                "leads.lead_source_id",
                "leads.assigned_to",
                "leads.firstname",
                "leads.lastname",
                DB::raw("TRIM(CONCAT(COALESCE(leads.firstname, ''), ' ', COALESCE(leads.lastname, ''))) AS full_name"),
                "leads.mobile",
                "leads.email",
                "leads.created_at",
                "leads.updated_at",
                "leads.deleted_at"
            )
            ->when($this->createdAtFrom, function ($query, $from) {
                $query->where('created_at', '>=', $from);
            })
            ->when($this->createdAtTo, function ($query, $to) {
                $query->where('created_at', '<=', $to);
            })
            ->orderBy('leads.updated_at', 'DESC')
            ->orderBy('leads.created_at', 'DESC');
    }
}
