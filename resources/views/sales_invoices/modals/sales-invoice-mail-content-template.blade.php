<p>Dear Admin,</p>
<p>A new invoice has been created and submitted by <strong>{{ Auth::user()->first_name.' '.Auth::user()->last_name }}</strong>.</p>
<p><strong>Invoice Details:</strong>
    <strong>Invoice No:</strong> #{{ $invoice->sales_invoice_number }}<br>
    <strong>Client Name:</strong> {{ $invoice->client->name ?? $invoice->client_name }}<br>
    <strong>Created By:</strong> {{ $invoice->salesperson->name ?? Auth::user()->first_name.' '.Auth::user()->last_name }}<br>
    <strong>Invoice Date:</strong> {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d-m-Y') }}<br>
    <strong>Due Date:</strong> {{ \Carbon\Carbon::parse($invoice->due_date)->format('d-m-Y') }}<br>
    <strong>Currency:</strong> {{ $invoice->currency->code }}<br></p>
<p><strong>Financial Summary:</strong><br>
    <strong>Subtotal:</strong> {{$invoice->currency->symbol}}{{ number_format($invoice->sub_total, 2) }}<br>
    <strong>VAT Total:</strong> {{$invoice->currency->symbol}}{{ number_format($invoice->vat_total, 2) }}<br>
    <strong>Grand Total:</strong> {{$invoice->currency->symbol}}{{ number_format($invoice->grand_total, 2) }}</p>
<p>You can view the full invoice details by logging into the admin panel.</p>
<p>Thanks<br>
{{Auth::user()->first_name.' '.Auth::user()->last_name}}</p>