@extends('emails.main-layout')

@section('content')
    <p
        style="margin: 0 0 20px;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
        <b style="font-weight: 600;">User has logged in using below details.</b>
    </p>

    <table width="100%" border="0" cellpadding="0" cellpadding="0">
        <tr>
            <td>
                <p
                    style="margin: 0;margin-bottom: 10px;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                    Login details</p>
            </td>
        </tr>
        <tr>
            <td>
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="padding-left: 20px;">
                    <tr>
                        <td>
                            <span
                                style="margin: 0;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                <b style="font-weight: 600;">Name</b>
                            </span>
                        </td>
                        <td>
                            <span
                                style="margin: 0;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                {{ $user->full_name ?? ($user->first_name ?? '') . ' ' . ($user->last_name ?? '') }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span
                                style="margin: 0;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                <b style="font-weight: 600;">Email</b>
                            </span>
                        </td>
                        <td>
                            <span
                                style="margin: 0;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                {{ $user->email ?? '' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span
                                style="margin: 0;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                <b style="font-weight: 600;">Browser</b>
                            </span>
                        </td>
                        <td>
                            <span
                                style="margin: 0;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                {{ $browser ?? '' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span
                                style="margin: 0;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                <b style="font-weight: 600;">Device</b>
                            </span>
                        </td>
                        <td>
                            <span
                                style="margin: 0;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                {{ $device ?? '' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span
                                style="margin: 0;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                <b style="font-weight: 600;">Device Type</b>
                            </span>
                        </td>
                        <td>
                            <span
                                style="margin: 0;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                {{ $deviceType ?? '' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span
                                style="margin: 0;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                <b style="font-weight: 600;">IP</b>
                            </span>
                        </td>
                        <td>
                            <span
                                style="margin: 0;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                {{ $ip ?? '' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span
                                style="margin: 0;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                <b style="font-weight: 600;">Is robot?</b>
                            </span>
                        </td>
                        <td>
                            <span
                                style="margin: 0;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                {{ $isRobot ? 'Yes' : 'No' }}</span>
                        </td>
                    </tr>
                    @if ($isRobot)
                        <tr>
                            <td>
                                <span
                                    style="margin: 0;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                    <b style="font-weight: 600;">Robot name</b>
                                </span>
                            </td>
                            <td>
                                <span
                                    style="margin: 0;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                    {{ $robot ?? '' }}</span>
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td>
                            <span
                                style="margin: 0;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                <b style="font-weight: 600;">Platform</b>
                            </span>
                        </td>
                        <td>
                            <span
                                style="margin: 0;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                {{ $platform ?? '' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span
                                style="margin: 0;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                <b style="font-weight: 600;">Latitude</b>
                            </span>
                        </td>
                        <td>
                            <span
                                style="margin: 0;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                {{ $lat ?? '' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span
                                style="margin: 0;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                <b style="font-weight: 600;">Longitude</b>
                            </span>
                        </td>
                        <td>
                            <span
                                style="margin: 0;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                {{ $long ?? '' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span
                                style="margin: 0;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                <b style="font-weight: 600;">Location</b>
                            </span>
                        </td>
                        <td>
                            <span
                                style="margin: 0;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                {{ $location ?? '' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span
                                style="margin: 0;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                <b style="font-weight: 600;">Host name:</b>
                            </span>
                        </td>
                        <td>
                            <span
                                style="margin: 0;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                {{ $hostname ?? '' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span
                                style="margin: 0;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                <b style="font-weight: 600;">Login Time</b>
                            </span>
                        </td>
                        <td>
                            <span
                                style="margin: 0;font-weight: 400;font-size: 14px;line-height: 1.4;color: #333;font-family: ''Public Sans', sans-serif;">
                                {{ $loginTime ?? '' }}</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
@endsection
