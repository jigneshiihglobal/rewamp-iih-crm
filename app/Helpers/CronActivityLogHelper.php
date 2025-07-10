<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Contracts\Activity;

class CronActivityLogHelper
{
    public static function log(
        string $event,
        string $description,
        array $properties = [],
        Request $request = null,
        User $causer = null,
        Model $subject = null,
        $workspace_id
    ) {

        if ($request) {
            $properties = array_merge($properties, self::userAgentAndIp($request));
            $properties = array_merge($properties, RequestHelper::getCurrentParsedUserAgent());
            $properties = array_merge($properties, [
                'hostname' => $_SERVER['HTTP_HOST'] ?? '',
            ]);
        }

        // $activity = activity()->event($event)->WithProperties($properties);
        $activity = activity()
            ->tap(function (Activity $activity) use ($event, $causer,$workspace_id) {
                $activity->event = $event;
                $activity->workspace_id = $workspace_id ?? null;
                $activity->latitude = isset($_COOKIE['posLat']) && !empty($_COOKIE['posLat']) ? $_COOKIE['posLat'] : null;
                $activity->longitude = isset($_COOKIE['posLon']) && !empty($_COOKIE['posLon']) ? $_COOKIE['posLon'] : null;
            })
            ->WithProperties($properties);

        if ($causer) {
            $activity = $activity->by($causer);
        }

        if ($subject) {
            $activity = $activity->on($subject);
        }

        $activity->log($description);
    }

    public static function userAgentAndIp(Request $request): array
    {
        return [
            "user_agent" => $request->header('User-Agent'),
            "ip_address" => $request->ip(),
        ];
    }
}
