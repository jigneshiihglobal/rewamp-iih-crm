<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"
    xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <title>@yield('title')</title>
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

        .email-container p,
        .email-container h1,
        .email-container h2,
        .email-container h3,
        .email-container h4,
        .email-container h5,
        .email-container h6 {
            margin: 0px;
            padding: 0px;
        }
    </style>
    @yield('styles')
</head>

<body width="100%" style="margin: 0; padding: 0 !important; mso-line-height-rule: exactly;background-color: #fff;">
    <div style="width:100%;">
        <div style="display:none;font-size:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;mso-hide:all;">
        </div>
        <div style="margin:0;padding:0px 0px;width:100%;" class="email-container">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0"
                style="margin:0px;background:#fff;max-width:100%;" class="primary">
                <tr>
                    <td>
                        {!! $followUp->content !!}
                    </td>
                </tr>
            </table>
        </div>
        <div style="max-width:600px;margin:0;padding:0px 0px;width:100%;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600"
                style="margin:auto;background:#fff;max-width:100%;" class="primary">
                <tr>
                    <td>
                        @include(
                            $followUp->email_signature->workspace->slug === 'shalin-designs'
                                ? 'emails.shalin-designs.invoices.user-email-signature'
                                : 'emails.invoices.user-email-signature')
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>
