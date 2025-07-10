<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogHelper;
use App\Helpers\DateHelper;
use App\Helpers\EncryptionHelper;
use App\Http\Requests\StoreUserRequest;
use App\Mail\SetPasswordMail;
use App\Models\Country;
use App\Models\User;
use App\Models\UserReview;
use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index(Request $request)
    {

        if ($request->ajax()) {
            $users = User::/*withTrashed()
                ->*/selectRaw('users.id, users.pic, users.first_name, users.last_name,users.email,users.is_active,  users.created_at,users.deleted_at, users.timezone, ( users.id = ? ) AS is_me', [
                    Auth::id()
                ])
                ->when(!Auth::user()->hasRole('Superadmin'), function ($query)
                {
                    $query->whereHas('workspaces', function ($query) {
                        $query->where('workspaces.id', Auth::user()->workspace_id);
                    });
                })
                ->withCount('leads');

            return DataTables::eloquent($users)
                ->editColumn(
                    'id',
                    function (User $user) {
                        return EncryptionHelper::encrypt($user->id);
                    }
                )
                ->editColumn(
                    'created_at',
                    function (User $user) {
                        return $user->created_at ? $user->created_at->setTimezone(Auth::user()->timezone)->format(DateHelper::USER_CREATED_DATE) : $user->created_at;
                    }
                )
                ->addColumn('full_name', function ($user) {
                    return $user->full_name;
                })
                ->addColumn('auth_user', function () {
                    return Auth::user()->roles->first()['name'];
                })
                ->addColumn('role', function ($user) {
                    if($user->hasRole('Superadmin')){
                        $user_role = "Superadmin";
                    }elseif($user->hasRole('Admin')){
                        $user_role = "Admin";
                    }elseif($user->hasRole('Marketing')){
                        $user_role = "Marketing";
                    }else{
                        $user_role = "User";
                    }
                    return $user_role;
                })
                ->filterColumn(
                    'full_name',
                    function ($query, $keyword) {
                        $query->where('first_name', 'like', "%{$keyword}%")
                            ->orWhere('last_name', 'like', "%{$keyword}%")
                            ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$keyword}%")
                            ->orWhere(DB::raw("CONCAT(last_name, ' ', first_name)"), 'like', "%{$keyword}%");
                    }
                )
                ->filterColumn(
                    'created_at',
                    function ($query, $keyword) {
                        $format = DateHelper::USER_CREATED_DATE_MYSQL;
                        $timezone_offset = DateHelper::getGmtOffsetFromTimezone(Auth::user()->timezone);
                        $query->orWhereRaw("DATE_FORMAT(CONVERT_TZ(users.created_at, '+00:00', '{$timezone_offset}'), '{$format}') like '%{$keyword}%'");
                    }
                )
                ->toJson();
        }

        return view("users.index", [
            'workspaces' => Workspace::all(['id', 'name', 'slug'])
        ]);
    }

    public function show(Request $request, User $user)
    {
        $countries = Country::all(['id', 'name']);
        $workspaces = Workspace::all(['id', 'name', 'slug']);
        $user->load(['workspaces:id,name,slug', 'email_signatures:id,user_id,name,email,position,mobile_number,image_link,sign_name,workspace_id']);
        // This same view is also used in own profile view and
        // accesses are managed conditionally
        // PLEASE READ BEFORE UPDATING
        return view('profile.index', compact('user', 'countries', 'workspaces'));
    }

    public function store(StoreUserRequest $request)
    {
        DB::beginTransaction();
        try {
            $valid = $request->safe()->except(['role']);
            $valid['name'] = $valid['first_name'] . ' ' . $valid['last_name'];
            $valid['password'] = Hash::make(Str::random(10));
            $user = User::create(Arr::except($valid, ['workspaces']));
            if(Auth::user()->hasRole('Superadmin')) {
                $user->workspaces()->attach($valid['workspaces']);
                if(count($valid['workspaces']) > 1) {
                    $workspaceSlug = 'iih-global';
                } else {
                    $ws = Workspace::whereIn('id', $valid['workspaces'])->first();
                    $workspaceSlug = $ws ? $ws->slug : 'iih-global';
                }
            } else {
                $user->workspaces()->attach(1);
                $workspaceSlug = 'iih-global';
            }
            $token = Str::random(60);
            $user->set_password_token = $token;
            $user->save();
            $role = $request->safe()['role'];
            $user->syncRoles($role);
            Mail::to($user)->send(new SetPasswordMail(route('set-password', ['token' => $token]), $workspaceSlug, $user->full_name));
            ActivityLogHelper::log(
                'users.created',
                'User is created by admin',
                [],
                $request,
                Auth::user(),
                $user
            );
            DB::commit();
            return response()->json([
                'success' => true
            ], 201);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error($th);
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
                'error' => $th
            ], 500);
        }
    }

    public function destroy(Request $request, User $user)
    {
        $user->loadCount('leads');
        abort_if($user->leads_count > 0, 400, "User has leads assigned!");
        try {
            $user->delete();

            ActivityLogHelper::log(
                'user.soft-deleted',
                'User is deleted.',
                [],
                $request,
                Auth::user(),
                $user
            );

            return response()
                ->json([
                    'success' => true
                ]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()
                ->json([
                    'success' => false,
                    'message' => $th->getMessage(),
                    'error' => $th
                ], 500);
        }
    }

    public function restore(Request $request, User $user)
    {
        try {
            $user->restore();

            ActivityLogHelper::log(
                'user.restored',
                'User is restored.',
                [],
                $request,
                Auth::user(),
                $user
            );
            return response()
                ->json([
                    'success' => true
                ]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()
                ->json([
                    'success' => false,
                    'message' => $th->getMessage(),
                    'error' => $th
                ], 500);
        }
    }

    public function forceDestroy(Request $request, User $user)
    {
        $user->loadCount('leads');
        abort_if($user->leads_count > 0, 400, "User has leads assigned!");
        try {
            $user->forceDelete();

            ActivityLogHelper::log(
                'user.permanently-deleted',
                'User is permanently deleted.',
                [
                    'user' => $user
                ],
                $request,
                Auth::user()
            );

            return response()
                ->json([
                    'success' => true
                ]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()
                ->json([
                    'success' => false,
                    'message' => $th->getMessage(),
                    'error' => $th
                ], 500);
        }
    }

    public function reviewStore(Request $request,User $user)
    {
        try {
            $reviewDate = Carbon::createFromFormat('d/m/Y', $request->review_date)->format('Y-m-d');
            $WonUserReview = new UserReview();
            $WonUserReview->user_id = $user->id ?? '';
            $WonUserReview->review_is = $request->review_is;
            $WonUserReview->client_name = $request->client_name;
            $WonUserReview->review = $request->Review;
            $WonUserReview->review_date = $reviewDate;
            $WonUserReview->save();

            ActivityLogHelper::log(
                "review.store",
                "Review (". $request->Review . ") added by " . Auth::user()->full_name ." for ". $user->name,
                [],
                $request,
                Auth::user(),
                $WonUserReview
            );

            return response()->json([
                "success" => true,
            ], 201);

        }catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                "success" => false,
                "message" => $th->getMessage(),
                "error" => $th,
            ], 500);
        }
    }
    public function userReview(Request $request,User $user)
    {
        try {
            $reviewLead = UserReview::select('review')
                ->where('user_id',$user->id)
                ->sum('review');

            return response()->json($reviewLead);
        }catch (\Throwable $th) {
            throw $th;
            return response()->json([], 500);
        }
    }
}
