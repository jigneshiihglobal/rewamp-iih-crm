@extends('emails.main-layout')

@section('content')
    <p>Hi {{ $invoice['customer_name'] ?? '' }},</p>
    <p>Thanks for your payment of <strong>{{ strtoupper($invoice['currency'] ?? 'USD') }} {{ $invoice['amount_received'] ?? '' }}</strong> via <strong>{{ $invoice['payment_method'] ?? '' }}</strong>.</p>
    <p>We’ve successfully received your payment. Below are your transaction details:</p>
    <ul>
        <li><strong>Payment ID:</strong> {{ $invoice['payment_id'] ?? '' }}</li>
        <li><strong>Status:</strong> {{ ucfirst($invoice['status'] ?? '') }}</li>
        <li><strong>Email:</strong> {{ $invoice['customer_email'] ?? '' }}</li>
        <li><strong>Payment Source:</strong> {{ $invoice['payment_source'] ?? '' }}</li>
    </ul>
    <h3>Payment Payload:</h3>
    <pre>{{ json_encode($payload, JSON_PRETTY_PRINT) }}</pre>
@endsection
