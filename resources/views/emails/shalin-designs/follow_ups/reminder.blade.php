@extends('emails.shalin-designs.main-layout')

@section('content')
    <p style="line-height: 1.4rem;">
        You need to do a follow up call with <strong>{{ $followUp->lead->firstname }} {{ $followUp->lead->lastname }}</strong> at <strong>{{ $followUp->follow_up_at->timezone($followUp->sales_person->timezone)->format(App\Helpers\DateHelper::FOLLOW_UP_DATE) }}</strong>.
    </p>
@endsection
