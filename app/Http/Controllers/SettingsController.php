<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogHelper;
use App\Http\Requests\StoreSystemSettingRequest;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    public function edit()
    {
        return view('system-settings.edit', ['system_settings' => SystemSetting::first()]);
    }

    public function update(StoreSystemSettingRequest $request)
    {
        $valid = $request->validated();
        try {
            $system_settings                            = SystemSetting::first() ?? new SystemSetting;
            $system_settings->whitelisted_ips           = $valid['whitelisted_ips'];
            $system_settings->login_mail_recipients     = $valid['login_mail_recipients'];
            $system_settings->save();

            ActivityLogHelper::log(
                'system-settings.updated',
                'Superadmin updated system settings.',
                [],
                $request,
                Auth::user(),
                $system_settings
            );

            return response()->json(['system_setting' => $system_settings]);
        } catch (\Throwable $th) {
            Log::info($th);

            return response()
                ->json([
                    'message'   => 'Unable to update system settings.',
                    'status'    => 'Something went wrong!',
                    'error'     => $th,
                ], 500);
        }
    }
}
