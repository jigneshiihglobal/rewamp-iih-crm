<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"
    xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <title>Friendly Reminder: Unpaid Invoice {{ $invoice->invoice_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <style type="text/css">
        body {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            font-size: 14px;
            line-height: 1.2;
            color: #333;
            font-family: 'Public Sans', sans-serif;
            font-weight: 400;
        }

        *,
        body * {
            font-family: 'Public Sans', sans-serif;
        }


        a {
            text-decoration: none;
        }

        img {
            max-width: 100%;
            display: inline-block;
            /* height: auto; */
        }


        table,
        tr,
        td,
        th {
            border: 0;
        }

        .custom-btn-primary {
            border-color: #f6931d !important;
            background-color: #f6931d !important;
            color: #fff !important;
            box-shadow: none;
            font-weight: 500;
            display: inline-block;
            line-height: 1;
            text-align: center;
            vertical-align: middle;
            cursor: pointer;
            user-select: none;
            padding: 0.786rem 1.5rem;
            font-size: 1rem;
            border-radius: 0.358rem;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out, background 0s, border 0s;
        }

        @media only screen and (max-width: 767px) {
            table.responsive-table {
                width: 100% !important;
            }

            .w-100 {
                width: 100% !important;
            }

            .display-nn {
                display: none !important;
            }

            .disblck {
                display: block !important;
            }
        }

        @media only screen and (max-width: 480px) {
            .footer_logo {
                width: 40px !important;
                height: 40px !important;
                margin-right: 30px !important;
            }
        }
    </style>
</head>

<body width="100%" style="margin: 0; padding: 0 !important; mso-line-height-rule: exactly;background-color: #fff;">
    <div style="width:100%;">
        <div style="display:none;font-size:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;mso-hide:all;">
        </div>
        <div style="max-width:600px;width:100%;" class="email-container">
            <table class="main_content" cellpadding="0" cellspacing="0" border="0" width="100%">
                <tr>
                    <td>
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td style="border: 0;background-color: #fff;">
                                    <p>Dear {{ $invoice->client_name ?? $invoice->client->name }},</p>

                                    <p>We hope this email finds you well. We would like to draw your attention to an
                                        unpaid invoice
                                        {{ $invoice->invoice_number ?? '' }} that is currently outstanding. We
                                        understand that oversights can happen, and we
                                        kindly remind you of the payment due.</p>
                                    @if (isset($invoice->payment_link) && !empty($invoice->payment_link))
                                        <a class='custom-btn-primary' href="{{ $invoice->payment_link }}"
                                            id="mail_payment_link">Pay now!</a>
                                    @endif

                                    <p>Attached is the invoice once again for your reference.</p>

                                    <p>Prompt payment is crucial for maintaining a healthy working relationship,
                                        ensuring smooth operations, and continuing
                                        to deliver the high-quality service you expect from us.</p>

                                    <p>Should you have any questions or require further clarification regarding the
                                        invoice or payment process, please feel
                                        free to reach out to us.</p>

                                    <p>Thank you for your prompt action, and we look forward to your payment.</p>

                                    <p>Best regards,</p>

                                    @include('emails.email-signature')
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>
