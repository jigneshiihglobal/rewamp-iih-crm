<?php

namespace App\Http\Controllers\Auth;

use App\Contracts\GeocodingAPI;
use App\Helpers\ActivityLogHelper;
use App\Helpers\CronActivityLogHelper;
use App\Helpers\RequestHelper;
use App\Http\Controllers\Controller;
use App\Mail\LoginInfoToAdminMail;
use App\Models\SystemSetting;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Traits\RedirectsToDashboard;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers, RedirectsToDashboard;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('check_location')->only('login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (
            method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)
        ) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            if ($request->hasSession()) {
                $request->session()->put('auth.password_confirmed_at', time());
            }
            $this->addActivityLog(
                $request,
                "login",
                "User logged in.",
                [],
            );

            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        if (request('logout_cause')) {
            session()->flash('message', 'User logged out!');
            session()->flash('type', 'error');
            session()->flash('status', request('logout_cause'));
        }
        return view('auth.login');
    }

    protected function addActivityLog(
        Request $request,
        string $event = "",
        string $description = "",
        array $properties = [],
        User $causer = null
    ) {
        ActivityLogHelper::log($event, $description, $properties, $request, $causer);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        $workspace_id = Auth::user()->workspace_id;
        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        CronActivityLogHelper::log(
            'logout',
            'User manually logged out.',
            [],
            $request,
            $user,
            null,
            $workspace_id
        );

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect('/');
    }


    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        $this->notifyAdminOfLogin($request, $user);
    }

    public function notifyAdminOfLogin(Request $request, $user)
    {
        $super = User::role('Superadmin')->first();
        try {
            $ip = $request->ip();
            $hostname = $_SERVER['HTTP_HOST'] ?? '';
            $system_setting = SystemSetting::firstOrFail();
            if (in_array($ip, $system_setting->whitelisted_ips ?? [])) return;
            $parsedUA = RequestHelper::getCurrentParsedUserAgent() ?? [];
            $location = "";
            $geocodingService = App::make(GeocodingAPI::class);

            try {
                $location = $geocodingService->reverseGeocode(
                    (float) ($_COOKIE['posLat'] ?? ''),
                    (float) ($_COOKIE['posLon'] ?? '')
                );
            } catch (\Throwable $th) {
                $location = '';
            }

            Mail::to($system_setting->login_mail_recipients ?? [])
                ->send(new LoginInfoToAdminMail(
                    $parsedUA['browser'] ?? '',
                    $parsedUA['device'] ?? '',
                    $parsedUA['deviceType'] ?? '',
                    $ip,
                    $parsedUA['isRobot'] ?? '',
                    now()->timezone(config('custom.system_settings.mail_timezone', 'Asia/Kolkata'))->format('d/m/Y H:i'),
                    $parsedUA['platform'] ?? '',
                    $parsedUA['robot'] ?? '',
                    $user,
                    $_COOKIE['posLat'] ?? '',
                    $_COOKIE['posLon'] ?? '',
                    $hostname ?? '',
                    $location ?? ''
                ));

            ActivityLogHelper::log('mail_sent.user_login', 'Mail sent to ' . implode(', ', $system_setting->login_mail_recipients ?? []) . ' [User logged in from other IP]', [], $request, $super, Auth::user());
        } catch (\Throwable $th) {
            Log::info($th);
            ActivityLogHelper::log('mail_send_fail.user_login', 'Mail send failed to ' . implode(', ', $system_setting->login_mail_recipients ?? []) . ' [User logged in from other IP]', [], $request, $super, Auth::user());
        }
    }
}
