@if ($model->subject_type)
    @if ($model->subject)
        @switch($model->subject_type)
            @case(\App\Models\Lead::class)
                <span class="fw-bolder">Lead Name: </span> {{ $model->subject->firstname ?? '' }}
                {{ $model->subject->lastname ?? '' }}
            @break

            @case(\App\Models\LeadNote::class)
                <span class="fw-bolder">Lead Note: </span> {!! Str::limit($model->subject->note ?? '', 20, '...') !!}
            @break

            @case(\App\Models\User::class)
                <span class="fw-bolder">User Name: </span> {{ $model->subject->full_name ?? '' }}
            @break

            @case(\App\Models\LeadStatus::class)
                <span class="fw-bolder">Status: </span> {!! $model->subject->badge ?? '' !!}
            @break

            @case(\App\Models\Client::class)
                {!! $model->subject->name ?? '' !!}
            @break

            @case(\App\Models\Invoice::class)
                @switch($model->event)
                    @case('invoices.payment-link.updated')
                        <span class="fw-bolder">Payment link updated for Invoice #{{ $model->subject->invoice_number ?? '' }}</span>
                    @break

                    @default
                        <span class="fw-bolder">Invoice #{{ $model->subject->invoice_number ?? '' }}</span>
                @endswitch
            @break

            @case(\App\Models\Payment::class)
                @switch($model->event)
                    @case('invoices.payments.paymentUpdate')
                        <span class="fw-bolder">Payment receipt has been updated for this Invoice #{{ $model->subject->invoice->invoice_number ?? '' }}</span>
                    @break

                    @case('invoices.payments.paymentDeleted')
                        <span class="fw-bolder">Removed payment {{ $model->subject->invoice->currency->symbol }}{{ number_format($model->subject->amount ?? '0.00', 2, '.', '') }}
                            in Invoice #{{ $model->subject->invoice->invoice_number ?? '' }}</span>
                    @break

                    @default
                        <span class="fw-bolder">
                            {{ $model->subject->invoice->currency->symbol }}{{ number_format($model->subject->amount ?? '0.00', 2, '.', '') }}
                            Received for Invoice #{{ $model->subject->invoice->invoice_number ?? '' }}
                        </span>
                @endswitch
            @break

            @case(\App\Models\FollowUp::class)
                <span class="fw-bolder">Lead: </span> {{ $model->subject->lead->full_name ?? '' }}<br />
            @break

            @case(\App\Models\EmailSignature::class)
                <span class="fw-bolder">User: </span> {{ $model->subject->user->first_name . " " . $model->subject->user->last_name }}<br />
            @break

            @case(\App\Models\Expense::class)
                <span class="fw-bolder">Expanse name: </span> {{ $model->subject->client->name  }} ({{$model->subject->project_name ?? ''}})
            @break

            @case(\App\Models\UserReview::class)
                <span class="fw-bolder">User review added </span>
            @break

            @case(\App\Models\SalesUserInvoice::class)
                <span class="fw-bolder">Sales Invoice: </span> #{{ $model->subject->sales_invoice_number ?? '' }}<br />
            @break

            @default
                -
        @endswitch
    @else
        This subject is permanently deleted!
    @endif
@else
    -
@endif
