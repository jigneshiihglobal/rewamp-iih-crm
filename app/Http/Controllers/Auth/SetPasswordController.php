<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\ActivityLogHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SetPasswordController extends Controller
{
    public function showSetPasswordForm(Request $request)
    {
        return view('auth.passwords.set', ['token' => $request->query('token')]);
    }

    public function setPassword(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed']
        ]);
        $user = User::where('set_password_token', $data['token'])->first();
        if (!$user) {
            return redirect()
                ->back()
                ->with([
                    'message' => 'Please contact admin',
                    'status' => 'Invalid set password link!',
                    'type' => 'error'
                ]);
        }
        DB::beginTransaction();
        try {
            $user->password = Hash::make($data['password']);
            $user->set_password_token = null;
            $user->save();

            ActivityLogHelper::log(
                'user.password-set',
                'User set his password.',
                [],
                $request,
                $user,
                null
            );

            DB::commit();
            Auth::login($user);

            return redirect()
                ->route('leads.index')
                ->with([
                    'status' => 'Congratulations!',
                    'type' => 'success',
                    'message' => 'Password set successfully!'
                ]);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error($th);
            ActivityLogHelper::log(
                'user.password-set.failed',
                'User failed to set his password.',
                [],
                $request,
                $user,
                null
            );
            return redirect()
                ->back()
                ->with([
                    'message' => 'Something went wrong',
                    'status' => 'Unable to set password!',
                    'type' => 'error'
                ]);
        }
    }
}
