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
    </style>
    @yield('styles')
</head>

<body width="100%" style="margin: 0; padding: 0 !important; mso-line-height-rule: exactly;background-color: #fff;">
    <center style="width:100%;">
        <div style="display:none;font-size:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;mso-hide:all;">
        </div>
        <div style="max-width:600px;margin:0 auto;width:100%;" class="email-container">
            <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="600"
                style="margin:auto;background:#fff;max-width:100%;" class="primary">
                <tr>
                    <td>
                        <table class="main_logo" cellpadding="0" cellspacing="0" border="0" width="100%">
                            <tr>
                                <td style="height:40px; line-height: 40px;" height="40px">&nbsp;</td>
                            </tr>
                            <tr>
                                <td align="center">
                                    <a href="#">
                                        @include('emails.logo')
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td style="height:40px; line-height: 40px;" height="40px">&nbsp;</td>
                            </tr>
                        </table>
                        <table class="main_content" cellpadding="0" cellspacing="0" border="0" width="100%">
                            <tr>
                                <td style="padding: 0 40px;">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td
                                                style="padding: 30px;border: 2px solid rgba(208, 221, 224, 0.2);background-color: #fff;">
                                                @yield('content')
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding-top: 20px;">
                                                <p
                                                    style="font-weight: 400;font-family: 'Public Sans', sans-serif;font-size: 12px;line-height: 1.4;color: #8A9294;text-align: center;margin: 0;">
                                                    © {{ date('Y') }} Shalin Designs</p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </center>
</body>

</html>
