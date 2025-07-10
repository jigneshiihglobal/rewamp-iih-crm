<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogHelper;
use App\Helpers\DateHelper;
use App\Helpers\FileHelper;
use App\Http\Requests\UpdateUserProfileRequest;
use App\Models\Country;
use App\Models\EmailSignature;
use App\Models\SmtpCredential;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Helpers\EncryptionHelper;
use Carbon\Carbon;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $countries = Country::all(['id', 'name']);
        $workspaces = Workspace::all(['id', 'name', 'slug']);
        $user->load('workspaces:id,name,slug');

        return view('profile.index', compact('user', 'countries', 'workspaces'));
    }

    public function update(UpdateUserProfileRequest $request, User $user = null)
    {
        $data = $request->validated();
        $user = $this->isAdminOrUpdatingSelf($user);
        $data['dob'] = $data['dob']
            ? date_create_from_format(DateHelper::DOB_DATE_FORMAT, $data['dob'])
            : null;
        $user->update($data);


        ActivityLogHelper::log(
            'users.profile.updated',
            Auth::id() == $user->id
                ? 'User updated his profile.'
                : "Admin updated user's profile.",
            [],
            $request,
            null,
            $user
        );

        if ($request->ajax()) {
            return response()->json([
                'success'   => true,
                'user'      => [
                    'first_name'    => $user->first_name,
                    'last_name'     => $user->last_name,
                    'email'         => $user->email,
                    'dob'           => $user->dob ? $user->dob->format(DateHelper::DOB_DATE_FORMAT) : '',
                    'gender'        => $user->gender,
                    'address'       => $user->address,
                    'city'          => $user->city,
                    'state'         => $user->state,
                    'country'       => $user->country,
                    'postal'        => $user->postal,
                    'phone'         => $user->phone,
                    'timezone'      => $user->timezone
                ]
            ]);
        }
        return redirect()->back();
    }

    public function updatePicture(Request $request, User $user = null)
    {
        $request->validate(['file' => 'required|file|mimes:png,jpg,jpeg']);
        $file = $request->file('file');
        $filename = time() . '-' . bin2hex(random_bytes(10)) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs(FileHelper::PROFILE_PIC_DIR, $filename, 'public');
        $user = $this->isAdminOrUpdatingSelf($user);

        if ($oldpic = $user->pic) {
            if (Storage::disk('public')->exists($oldpic)) {
                Storage::disk('public')->delete($oldpic);
            }
        }

        $user->update(['pic' => $path]);

        ActivityLogHelper::log(
            'users.profile.picture.updated',
            Auth::id() == $user->id
                ? 'User updated his profile picture.'
                : "Admin updated user's profile picture.",
            [],
            $request,
            null,
            $user
        );

        return response()->json(['success' => true, 'path' => url('storage/' . $path,)]);
    }

    public function removePicture(Request $request, User $user = null)
    {
        $user = $this->isAdminOrUpdatingSelf($user);
        if ($oldpic = $user->pic) {
            if (Storage::disk('public')->exists($oldpic)) {
                Storage::disk('public')->delete($oldpic);
            }
        }
        $user->update(['pic' => null]);

        ActivityLogHelper::log(
            'users.profile.picture.deleted',
            Auth::id() == $user->id
                ? 'User deleted his profile picture.'
                : "Admin deleted user's profile picture.",
            [],
            $request,
            null,
            $user
        );

        return response()->json(['success' => true]);
    }

    public function updatePassword(Request $request, User $user = null)
    {
        $request->validate([
            'password' => [
                'required',
                'string',
                'min:6',
                'confirmed'
            ]
        ]);
        $user = $this->isAdminOrUpdatingSelf($user);
        $hash = Hash::make($request->password);
        $user->update(['password' => $hash]);

        ActivityLogHelper::log(
            'users.password.updated',
            Auth::id() == $user->id
                ? 'User updated his password.'
                : "Admin updated user's password.",
            [],
            $request,
            null,
            $user
        );

        return response()->json([
            'success' => true
        ]);
    }

    public function updateGroup(Request $request, User $user = null)
    {
        $request->validate([
            'group' => [
                'required',
                'exists:roles,id'
            ],
            'is_active' => ['boolean']
        ]);

        $user = $this->isAdminOrUpdatingSelf($user);
        $user->syncRoles($request->group);
        $user->update([
            'is_active' => $request->boolean('is_active', false),
            'is_invoice_access' => $request->boolean('is_invoice_access', false)
        ]);

        ActivityLogHelper::log(
            'users.group.updated',
            "Admin has updated User's role.",
            [],
            $request,
            null,
            $user
        );

        return response()->json([
            'success' => true
        ]);
    }

    public function updateWorkspaceAccess(Request $request, User $user = null)
    {
        $valid = $request->validate([
            'workspaces' => 'required|array|min:1',
            'workspaces.*' => 'required|exists:workspaces,id'
        ]);

        DB::beginTransaction();

        try {

            $user->workspaces()->sync($valid['workspaces']);

            ActivityLogHelper::log(
                'user.workspace.updated',
                "Superadmin updated user's workspace access",
                [],
                $request,
                Auth::user(),
                $user
            );

            DB::commit();

            return response()->json([
                'success' => true
            ]);
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
                'error' => $th
            ], 500);
        }
    }

    public function updateSignature(Request $request, User $user = null)
    {
        $user = $this->isAdminOrUpdatingSelf($user);
        $valid = $request->validate([
            'email_signatures'          => 'required|array|min:1',
            'email_signatures.*'        => 'required|array|min:1',
            'email_signatures.*.id'     => [
                'nullable',
                function ($attribute, $value,  $fail) use ($user) {
                    if ($user->email_signatures()->where('id', $value)->doesntExist())  $fail("User is not authorized");
                }
            ],
            'email_signatures.*.sign_name'          => [
                'required',
                'string',
                'min:2',
                function ($a, $v, $f) use ($user, $request) {
                    $idAttr = str_replace("sign_name", "id", $a);
                    $email_signature_id = $request->input($idAttr);
                    $nameExists = EmailSignature::where("sign_name", $v)->where('user_id', $user->id)->when($email_signature_id, function ($q, $email_signature_id) {
                        $q->where('id', "!=", $email_signature_id);
                    })->exists();
                    if ($nameExists) $f('Please enter unique signature name');
                }
            ],
            'email_signatures.*.name'               => 'required|string|min:2',
            'email_signatures.*.position'           => "required|string|min:2",
            'email_signatures.*.email'              => 'required|email',
            'email_signatures.*.mobile_number'      => 'required|array|min:1',
            'email_signatures.*.mobile_number.*'    => 'required|string|min:8|max:20|regex:/^(\+)?([0-9]+(\s)?)+$/',
            'email_signatures.*.image_link'         => 'nullable|url',
        ]);

        DB::beginTransaction();

        try {

            $emailSignatures = $valid['email_signatures'];
            $keep = [];

            foreach ($emailSignatures as $key => $emailSignature) {
                $emailSignature['mobile_number'] = implode('|', $emailSignature['mobile_number']);
                if ($emailSignature['id']) {
                    $email_signature = EmailSignature::findOrFail($emailSignature['id']);
                    $email_signature->update($emailSignature);
                    ActivityLogHelper::log(
                        'users.email-signature.updated',
                        "User's email signature updated.",
                        [],
                        $request,
                        Auth::user(),
                        $email_signature
                    );
                    array_push($keep, $email_signature->id);
                } else {
                    $email_signature = $user->email_signatures()->create($emailSignature);
                    ActivityLogHelper::log(
                        'users.email-signature.created',
                        "User's email signature created.",
                        [],
                        $request,
                        Auth::user(),
                        $email_signature
                    );
                    array_push($keep, $email_signature->id);
                }
            }

            $user->email_signatures()->whereNotIn('id', $keep)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
            ]);
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
                'error' => $th
            ], 500);
        }
    }

    public function updateSmtp(Request $request, User $user = null)
    {
        $user = $this->isAdminOrUpdatingSelf($user);
        $valid = $request->validate([
            'smtp_settings'         => 'required|array|min:1',
            'smtp_settings.*'       => 'required|array:id,host,port,encryption,username,secret,smtp_name,from_name,from_address',
            'smtp_settings.*.id'    => [
                'nullable',
                function ($attribute, $value,  $fail) use ($user) {
                    if ($user->smtp_credentials()->where('id', $value)->doesntExist())  $fail("Smtp credentials not found");
                }
            ],
            'smtp_settings.*.host'          => 'required|string',
            'smtp_settings.*.port'          => "required|numeric",
            'smtp_settings.*.encryption'    => 'required|string',
            'smtp_settings.*.username'      => 'required|string',
            'smtp_settings.*.secret'        => 'required|string',
            'smtp_settings.*.smtp_name'     => [
                "required",
                "string",
                function ($a, $v, $f) use ($user, $request) {
                    $idAttr = str_replace("smtp_name", "id", $a);
                    $smtp_credential_id = $request->input($idAttr);
                    $nameExists = SmtpCredential::where("smtp_name", $v)->where('user_id', $user->id)->when($smtp_credential_id, function ($q,  $smtp_credential_id) {
                        $q->where('id', "!=", $smtp_credential_id);
                    })->exists();
                    if ($nameExists) $f('Please enter unique smtp name');
                }
            ],
            'smtp_settings.*.from_name'     => 'required|string|min:3',
            'smtp_settings.*.from_address'  => 'required|string|email',
        ]);

        DB::beginTransaction();

        try {

            $smtp_settings = $valid['smtp_settings'];
            $keep = [];

            foreach ($smtp_settings as $key => $smtpSetting) {
                if ($smtpSetting['id']) {
                    $smtp_credential = SmtpCredential::findOrFail($smtpSetting['id']);
                    $smtp_credential->update($smtpSetting);
                    ActivityLogHelper::log(
                        'users.smtp-credential.updated',
                        "User's smtp credential updated.",
                        [],
                        $request,
                        Auth::user(),
                        $smtp_credential
                    );
                    array_push($keep, $smtp_credential->id);
                } else {
                    $smtp_credential = $user->smtp_credentials()->create($smtpSetting);
                    ActivityLogHelper::log(
                        'users.smtp-credential.created',
                        "User's smtp credential created.",
                        [],
                        $request,
                        Auth::user(),
                        $smtp_credential
                    );
                    array_push($keep, $smtp_credential->id);
                }
            }

            $user->smtp_credentials()->whereNotIn('id', $keep)->delete();

            DB::commit();

            return response()->json([
                'success' => true
            ]);
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
                'error' => $th
            ], 500);
        }
    }

    public function smtpDelete(Request $request)
    {
        $date = Carbon::now();
        $id = $request->id;
        $data = SmtpCredential::find($id);
        $data->update(['deleted_at'=>$date]);
    }

    private function isAdminOrUpdatingSelf(User $user = null)
    {
        if (!$user) {
            return Auth::user();
        }
        abort_if(!Auth::user()->hasRole(['Admin', 'Superadmin']) && $user->id != Auth::id(), 403);
        return $user;
    }
}
