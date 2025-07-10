<br />
<div style="width: 100%;max-width: 600px;">
    <table cellspacing="0" cellpadding="0" border="0" style="border-spacing: 0px;width:100%;">
        <tr>
            <td>
                <h2 style="margin: 0px;">{{ $email_signature->name ?? '' }}</h2>
            </td>
        </tr>
        <tr>
            <td>
                <h4 style="margin: 0px;">
                    @if ($email_signature && $email_signature->position)
                        <span class="user_role" style="color: #ff9237;">{{ $email_signature->position }} |</span>
                    @endif
                    <span class="company_name" style="color: #1f5e9a;">Intelligent IT Hub Pvt. Ltd.</span>
                </h4>
            </td>
        </tr>
    </table>
    <hr style="background-color: #acd7da; height: 1px; border:none;">
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
                <td valign="top" style="padding: 0px; width: 0%; vertical-align: middle">
                    <a href="https://www.iihglobal.com/"
                        style="
                          color: rgb(51, 122, 183);
                          background-color: transparent;
                        "
                        target="_blank">
                        <img border="0" height="86" src="{{ asset('app-assets/images/emails/full-logo.gif') }}"
                            style="
                            border: 0px;
                            vertical-align: middle;
                            height: 86px;
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
						border-left: 2px solid #f6931d;
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
                                            <span style="font-weight: bold; color: #f6931d">M:&nbsp;</span>
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
                                        <span style="font-weight: bold; color: #f6931d">A:&nbsp;</span>
                                        IIH Global, Rickmansworth, WD3 1RE, United Kingdom
                                    </span>
                                </td>
                            </tr>
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
                                        <span style="font-weight: bold; color: #f6931d">A:&nbsp;</span>
                                        IIH Global, Ahmedabad, 380060, India
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td
                                    style="font-family: Arial, sans-serif; padding: 0px 0px 1px; font-size: 12px; line-height: 18px; color: rgb(155, 155, 155);">
                                    <span style="color: rgb(60, 60, 59)" class="disblck">
                                        <span style="font-weight: bold; color: #f6931d">E:&nbsp;</span>
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
                                        <span style="font-weight: bold; color: #f6931d">W:&nbsp;</span>
                                        <a href="http://www.iihglobal.com/"
                                            style="
                                    text-decoration: underline;
                                    color: #000;
                                    background-color: transparent;
                                  "
                                            target="_blank">
                                            <span>www.<span class="il">iihglobal</span>.com</span>
                                        </a>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td
                                    style="
                                padding: 10px 0px 0px;
                                vertical-align: bottom;
                              ">
                                    <span style="display: inline-block; height: 22px">
                                        <a href="https://www.facebook.com/iihglobal/"
                                            style="
                                    color: rgb(51, 122, 183);
                                    background-color: transparent;
                                    text-decoration: none;
                                  "
                                            target="_blank">
                                            <img border="0" width="20" height="20"
                                                src="{{ asset('app-assets/images/emails/social-color/facebook.png') }}"
                                                style="
                                      border: 0px;
                                      vertical-align: middle;
                                      height: 20px;
                                      width: 20px;
                                    " />
                                        </a>&nbsp;&nbsp;
                                        <a href="https://twitter.com/IIH_Global"
                                            style="
                                    color: rgb(51, 122, 183);
                                    background-color: transparent;
                                    text-decoration: none;
                                  "
                                            target="_blank">
                                            <img border="0" width="20" height="20"
                                                src="{{ asset('app-assets/images/emails/social-color/twitter.png') }}"
                                                style="
                                      border: 0px;
                                      vertical-align: middle;
                                      height: 20px;
                                      width: 20px;
                                    " />
                                        </a>&nbsp;&nbsp;
                                        <a href="https://www.youtube.com/@iihglobal"
                                            style="
                                    color: rgb(51, 122, 183);
                                    background-color: transparent;
                                    text-decoration: none;
                                  "
                                            target="_blank">
                                            <img border="0" width="20" height="20"
                                                src="{{ asset('app-assets/images/emails/social-color/youtube.png') }}"
                                                style="
                                      border: 0px;
                                      vertical-align: middle;
                                      height: 20px;
                                      width: 20px;
                                    " />
                                        </a>&nbsp;&nbsp;
                                        <a href="https://www.linkedin.com/company/iihglobal/"
                                            style="
                                    color: rgb(51, 122, 183);
                                    background-color: transparent;
                                    text-decoration: none;
                                  "
                                            target="_blank">
                                            <img border="0" width="20" height="20"
                                                src="{{ asset('app-assets/images/emails/social-color/linkedin.png') }}"
                                                style="
                                      border: 0px;
                                      vertical-align: middle;
                                      height: 20px;
                                      width: 20px;
                                    " />
                                        </a>&nbsp;&nbsp;
                                        <a href="https://www.instagram.com/iih_global/"
                                            style="
                                    color: rgb(51, 122, 183);
                                    background-color: transparent;
                                    text-decoration: none;
                                  "
                                            target="_blank">
                                            <img border="0" width="20" height="20"
                                                src="{{ asset('app-assets/images/emails/social-color/instagram.png') }}"
                                                style="
                                      border: 0px;
                                      vertical-align: middle;
                                      height: 20px;
                                      width: 20px;
                                    " />
                                        </a>&nbsp;&nbsp;
                                        <a href="https://www.pinterest.co.uk/iihglobaluk/_saved/"
                                            style="
                                    color: rgb(51, 122, 183);
                                    text-decoration: none;
                                    background-color: transparent;
                                  "
                                            target="_blank">
                                            <img border="0" width="20" height="20"
                                                src="{{ asset('app-assets/images/emails/social-color/pinterest.png') }}"
                                                style="
                                      border: 0px;
                                      vertical-align: middle;
                                      height: 20px;
                                      width: 20px;
                                    " />
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
    <a href="{{ $email_signature->image_link ?? 'https://www.iihglobal.com/schedule-call/' }}">
        <img src="{{ asset('app-assets/images/emails/tree-planted-mail.png') }}"
            alt="A tree planted with every project awarded to us. Schedule a meeting."
            style="margin-top: 8px; width: 100%;">
    </a>
    <p style="font-size: 10px; color: #888;">The content of this email is confidential and intended for the recipient
        specified in message only. It is strictly forbidden to share any part of this message with any third party,
        without
        a written consent of the sender. If you received this message by mistake, please reply to this message and
        follow
        with its deletion, so that we can ensure such a mistake does not occur in the future.</p>
</div>
