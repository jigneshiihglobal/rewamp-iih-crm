<?php

namespace App\Helpers;

use Jenssegers\Agent\Agent;

class RequestHelper
{

    public static function getCurrentParsedUserAgent()
    {
        $agent = new Agent;
        $browser = $agent->browser();
        $browser = $browser . " " . $agent->version($browser);
        $device = $agent->device();
        $platform = $agent->platform();
        $platform = $platform . " " . $agent->version($platform);
        $isRobot = $agent->isRobot();
        $robot = $agent->robot();
        $deviceType = '';

        if ($agent->isMobile()) {
            $deviceType = 'Mobile';
        } else if ($agent->isDesktop()) {
            $deviceType = 'Desktop';
        } else if ($agent->isTablet()) {
            $deviceType = 'Tablet';
        } else if ($agent->isPhone()) {
            $deviceType = 'Phone';
        }


        return compact('browser', 'device', 'platform', 'isRobot', 'robot', 'deviceType');
    }
}
