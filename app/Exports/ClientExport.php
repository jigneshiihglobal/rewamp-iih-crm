<?php

namespace App\Exports;

use App\Helpers\DateHelper;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\FromQuery;

class ClientExport implements FromQuery, WithHeadings, WithMapping
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

    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Phone',
            'Address Line 1',
            'Address Line 2',
            'City',
            'Zip Code',
            'Country',
            'vat Number',
            'Created At',
            'Updated At',
        ];
    }

    public function map($client): array
    {
        return [
            $client->name ?? '',
            $client->email ?? '',
            $client->phone ?? '',
            $client->address_line_1 ?? '',
            $client->address_line_2 ?? '',
            $client->city ?? '',
            $client->zip_code ?? '',
            $client->country->name ?? '',
            $client->vat_number ?? '',
            $client->created_at ? $client->created_at->format(DateHelper::CLIENT_DATE_FORMAT)  : '',
            $client->updated_at ? $client->updated_at->format(DateHelper::CLIENT_DATE_FORMAT_MYSQL)  : '',
        ];
    }

    public function query()
    {
        return Client::select("clients.*")
                ->withTrashed()
                ->with(['country:id,name','projectList:id,customer_id,project_id'])
                ->withCount(['invoices','clientFeedbackMail'])
                ->where('workspace_id', Auth::user()->workspace_id)
                ->when($this->createdAtFrom, function ($query, $from) {
                    $query->where('created_at', '>=', $from);
                })
                ->when($this->createdAtTo, function ($query, $to) {
                    $query->where('created_at', '<=', $to);
                })
                ->orderBy('clients.updated_at', 'DESC')
                ->orderBy('clients.created_at', 'DESC');
    }
}
