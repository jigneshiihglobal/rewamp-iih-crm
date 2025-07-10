<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"
    xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="utf-8" />
    <!-- utf-8 works for most cases -->
    <meta name="viewport" content="width=device-width" />
    <!-- Forcing initial-scale shouldn't be necessary -->
    <meta http-equiv="x-ua-compatible" content="IE=edge" />
    <!-- Use the latest (edge) version of IE rendering engine -->
    <meta name="x-apple-disable-message-reformatting" />
    <!-- Disable auto-scale in iOS 10 Mail entirely -->
    <meta name="color-scheme" content="light dark" />
    <meta name="supported-color-schemes" content="light dark" />
    <meta name="theme-color" content="black" />
    <meta name="msapplication-navbutton-color" content="black" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <title>IIH Global</title>
    <style type="text/css">
        body {
            font-family: Arial, Helvetica, sans-serif;
        }

        a {
            text-decoration: none;
        }

        img {
            max-width: 100%;
            display: inline-block;
            height: auto;
        }

        table,
        tr,
        td,
        th {
            border: 0;
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

<body width="100%"
    style=" margin: 0; padding: 0 !important; mso-line-height-rule: exactly; background-color: hsl(0, 0%, 90%);">
    <div style="width: 100%;background-color: #fff;" class="email-container">
        <div style="max-width: 600px; width: 100%;">
            <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="600"
                style="
            background: #fff; max-width: 100%;"
                class="w-100">
                <!-- start email-content -->
                <tr>
                    <td class="header" style="padding-bottom: 20px;">
                        <table align="left" width="100%">
                            <tr>
                                <td align="left"
                                    style="
                      font-family: Arial, Helvetica, sans-serif;
                      font-size: 13px;
                      color: #000;
                      line-height: 20px;
                      font-weight:900;
                    ">
                                    Hi {{ $invoice->client_name ?? ($invoice->client->name ?? '') }},
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 2px 0"></td>
                            </tr>
                            <tr>
                                <td align="left"
                                    style="
                      font-family: Arial, Helvetica, sans-serif;
                      font-size: 13px;
                      color: #000;
                      line-height: 20px;
                      font-weight: 400;
                    ">
                                    I hope this email finds you well. We would like to express
                                    our gratitude for your recent payment. This email serves as
                                    a formal acknowledgment of your payment and a receipt for
                                    your records. Please find the attached.
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 2px 0"></td>
                            </tr>
                            <tr>
                                <td align="left"
                                    style="
                      font-family: Arial, Helvetica, sans-serif;
                      font-size: 13px;
                      color: #000;
                      line-height: 20px;
                      font-weight: 400;
                    ">
                                    We greatly appreciate your timely settlement, which helps us
                                    maintain the quality of our services.
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 2px 0"></td>
                            </tr>
                            <tr>
                                <td align="left"
                                    style="
                      font-family: Arial, Helvetica, sans-serif;
                      font-size: 13px;
                      color: #000;
                      line-height: 20px;
                      font-weight: 400;
                    ">
                                    Should you require any further information or have any
                                    queries regarding this payment or any other matter, please
                                    do not hesitate to contact our dedicated support team at

                                    <a href="mailto:accounts@iihglobal.com"
                                        target="_blank"style="text-decoration: underline; color: rgb(51, 122, 183) !important;">accounts@iihglobal.com</a>.

                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 2px 0"></td>
                            </tr>
                            <tr>
                                <td align="left"
                                    style="
                      font-family: Arial, Helvetica, sans-serif;
                      font-size: 13px;
                      color: #000;
                      line-height: 20px;
                      font-weight: 400;
                    ">

                                    Once again, we sincerely appreciate your business and look forward to serving you in the future. Thank you for your continued support.

                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <!-- END email-content -->
                <!-- START footer -->
                <tr mc:repeatable mc:hideable>
                    <td class="grid-section" style="background-color: #fff; padding: 0px">
                        @include('emails.email-signature')
                    </td>
                </tr>
                <!-- End footer -->
            </table>
        </div>
    </div>
</body>

</html>
