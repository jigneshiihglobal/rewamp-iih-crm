<br />
<div style="width: 100%;max-width: 600px;">
    <table>
        <tr>
            <td>
                <h2 style="margin: 0px 0px 8px 0px;">{{ $email_signature->name ?? '' }}</h2>
            </td>
        </tr>
        <tr>
            <td>
                <h4 style="margin: 0px;">
                    @if ($email_signature && $email_signature->position)
                        <span class="user_role" style="color: #2F5496;">{{ $email_signature->position }} |</span>
                    @endif
                    <span class="company_name" style="color: #1f5e9a;">Shalin Designs Limited</span>
                </h4>
            </td>
        </tr>
    </table>
    <hr style="background-color: #acd7da; height: 1px; border:none; margin-bottom: 16px;">
    <table cellpadding="0" cellspacing="0"
        style="
                  border-spacing: 0px;
                  border-collapse: collapse;
                  color: rgb(68, 68, 68);
                  width: 100%;
                  font-size: 12px;
                  font-family: Arial, sans-serif;
                  line-height: normal;
                ">
        <tbody>
            <tr>
                <td valign="top" style="padding: 0px; width: 70px; vertical-align: middle">
                    <a href="https://www.shalindesigns.com/"
                        style="
                          color: rgb(51, 122, 183);
                          background-color: transparent;
                        "
                        target="_blank">
                        <img border="0" width="140"
                            src="{{ asset('shalin-designs/img/full-logo.png') }}"
                            style="
                            border: 0px;
                            vertical-align: middle;
                            width: 140px;
                          "
                            class="footer_logo" />
                    </a>
                </td>
                <td valign="top"
                    style="
                        padding: 0px;
                        width: 15px;
                        text-align: center;
                        vertical-align: top;
						border-left: 2px solid #1f5e9a;
                      ">
                </td>
                <td valign="top" style="padding: 0px; width: 300px; vertical-align: top">
                    <table cellpadding="0" cellspacing="0"
                        style="
                          border-spacing: 0px;
                          border-collapse: collapse;
                          background-color: transparent;
                        ">
                        <tbody>
                            @if ($email_signature && $email_signature->mobile_number)
                                <tr>
                                    <td
                                        style="font-family: Arial, sans-serif; padding: 0px 0px 1px; font-size: 12px; line-height: 18px; color: rgb(155, 155, 155);">
                                        <span style="color: rgb(60, 60, 59)" class="disblck">
                                            <span style="font-weight: bold; color: #1f5e9a">M:&nbsp;</span>
                                            @php
                                                $mobileNumbersArr = explode('|', $email_signature->mobile_number);
                                            @endphp
                                            @foreach ($mobileNumbersArr as $mobile_number)
                                                @if (!empty($mobile_number))
                                                    <a href="tel:{{ $mobile_number }}"
                                                        style=" color: rgb(60, 60, 59);
                                text-decoration: underline;">{{ $mobile_number }}
                                                    </a>
                                                    @if (!$loop->last)
                                                        <span style="color: rgb(60, 60, 59)"
                                                            class="display-nn">&nbsp;|&nbsp;</span>
                                                    @endif
                                                @endif
                                            @endforeach
                                        </span>
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td
                                    style="
                                font-family: Arial, sans-serif;
                                padding: 0px 0px 1px;
                                font-size: 12px;
								line-height: 18px;
                                color: rgb(155, 155, 155);
                              ">
                                    <span style="color: rgb(60, 60, 59)">
                                        <span style="font-weight: bold; color: #1f5e9a">A:&nbsp;</span>
                                        Shalin Designs LTD, Cardinal Point, Park Road, <br>Rickmansworth WD3 1RE
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td
                                    style="font-family: Arial, sans-serif; padding: 0px 0px 1px; font-size: 12px; line-height: 18px; color: rgb(155, 155, 155);">
                                    <span style="color: rgb(60, 60, 59)" class="disblck">
                                        <span style="font-weight: bold; color: #1f5e9a">E:&nbsp;</span>
                                        <a href="mailto:{{ $email_signature && $email_signature->email ? $email_signature->email : '' }}"
                                            style="
                                    color: rgb(60, 60, 59);
                                    text-decoration: underline;
                                  "
                                            target="_blank">
                                            {{ $email_signature && $email_signature->email ? $email_signature->email : '' }}
                                        </a>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td
                                    style="
                                font-family: Arial, sans-serif;
                                padding: 0px 0px 1px;
                                font-size: 12px; line-height: 18px;
                                color: rgb(155, 155, 155);">
                                    <span style="color: rgb(60, 60, 59)" class="disblck">
                                        <span style="font-weight: bold; color: #1f5e9a">W:&nbsp;</span>
                                        <a href="https://www.shalindesigns.com/"
                                            style="
                                    text-decoration: underline;
                                    color: #000;
                                    background-color: transparent;
                                  "
                                            target="_blank">
                                            <span>www.<span class="il">shalindesigns</span>.com</span>
                                        </a>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr></tr>
        </tbody>
    </table>
</div>
