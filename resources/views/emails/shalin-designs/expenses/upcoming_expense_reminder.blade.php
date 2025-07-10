<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"
    xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <title>Expenses Due Soon</title>
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

        .expensesTable thead th {
            background-color: #fff3e6;
        }
    </style>
    @yield('styles')
</head>

<body width="100%" style="margin: 0; padding: 0 !important; mso-line-height-rule: exactly;background-color: #fff;">
    <center style="width:100%;">
        <div style="display:none;font-size:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;mso-hide:all;">
        </div>
        <div style="max-width:80%;margin:0 auto;width:100%;" class="email-container">
            <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="80%"
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
                                <td style="padding: 0 0;">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td
                                                style="padding: 30px;border: 2px solid rgba(208, 221, 224, 0.2);background-color: #fff;">

                                                <p
                                                    style="margin: 0 0 20px;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                                    <b style="font-weight: 600;">You have some expenses due soon: </b>
                                                </p>

                                                <table width="100%" border="0" cellpadding="0" cellpadding="0">
                                                    <tr>
                                                        <td>
                                                            <p
                                                                style="margin: 0;margin-bottom: 10px;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                                                Expense Details</p>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <table border="1" cellpadding="4" cellspacing="0"
                                                                width="100%" class="expensesTable">
                                                                <thead>
                                                                    <tr>
                                                                        <th>
                                                                            <span>Project</span>
                                                                        </th>
                                                                        <th>
                                                                            <span>Client</span>
                                                                        </th>
                                                                        <th>
                                                                            <span>Amount</span>
                                                                        </th>
                                                                        <th>
                                                                            <span>Type</span>
                                                                        </th>
                                                                        <th>
                                                                            <span>Frequency</span>
                                                                        </th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @forelse ($expenses as $expense)
                                                                        <tr>
                                                                            <td>
                                                                                <span>{{ $expense->project_name ?? '' }}</span>
                                                                            </td>
                                                                            <td>
                                                                                <span>{{ $expense->client->name ?? '' }}</span>
                                                                            </td>
                                                                            <td>
                                                                                <span>
                                                                                    {{ $expense->currency->symbol ?? '' }}{{ $expense->amount ? number_format((float) $expense->amount, 2, '.', ',') : $expense->amount }}
                                                                                </span>
                                                                            </td>
                                                                            <td>
                                                                                <span>

                                                                                    @if (isset($expense->expense_sub_type->expense_type))
                                                                                        {{ $expense->expense_sub_type->expense_type->title ?? '' }}
                                                                                        ({{ $expense->expense_sub_type->title ?? '' }})
                                                                                    @else
                                                                                        {{ $expense->expense_sub_type->title ?? '' }}
                                                                                    @endif
                                                                                </span>
                                                                            </td>
                                                                            <td>
                                                                                <span>
                                                                                    {{ $expense->type == App\Enums\ExpenseType::RECURRING ? 'Recurring' : 'One-off' }}
                                                                                    @if ($expense->type == App\Enums\ExpenseType::RECURRING)
                                                                                        ({{ $expense->frequency == App\Enums\ExpenseFrequency::YEARLY ? 'Yearly' : 'Monthly' }})
                                                                                    @endif
                                                                                </span>
                                                                            </td>
                                                                        </tr>
                                                                    @empty
                                                                        <tr>
                                                                            <td colspan="4">No expenses.</td>
                                                                        </tr>
                                                                    @endforelse
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>

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
