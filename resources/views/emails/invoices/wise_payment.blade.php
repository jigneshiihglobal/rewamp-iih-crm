@extends('emails.main-layout')

@section('content')
    <table>
        <tr>
            <td>
                <p>invoice number : {{$invoice->invoice_number}}</p>
            </td>
        </tr>
        <tr>
            <td>
                <p>invoice currency : {{$currency}}</p>
            </td>
        </tr>
        <tr>
            <td>
                <p>Invoice Receive Amount : {{number_format($amount,2)}}</p>
            </td>
        </tr>
    </table>
@endsection
