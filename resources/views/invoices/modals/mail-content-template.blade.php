<p><strong>Hi {{ $invoice->client_name ?? ($invoice->client->name ?? '') }},</strong></p>
<p>Hope you are doing well!</p>
<p>Please find the attached invoice to complete the payment formality.</p>
<p>We kindly request that you review the invoice at your earliest convenience and proceed with the payment before the due date. Your timely payment is greatly appreciated.</p>
@if(!empty($invoice->payment_link))
{{--<a class='custom-btn-primary' href="{{ empty($invoice->payment_link) ? '#' : $invoice->payment_link }}" id="mail_payment_link">Pay now!</a>--}}
<a class='custom-btn-primary paynowbtn' href="{{ empty($invoice->payment_link) ? '#' : $invoice->payment_link }}" id="mail_payment_link"><img src="{{ asset('app-assets/images/emails/credit-card.png') }}" style="margin-right: 10px;
    width: 18px;">Pay now!</a>
@endif
<p>Do let us know if you have any questions.</p>
<p>Thank you for allowing us an opportunity to serve you!</p>