@extends('emails.main-layout')

@section('content')
    <p>Database backup for {{ config('app.name') }} CRM generated. </p>
    <p>Please find it attached with this mail.</p>
@endsection
