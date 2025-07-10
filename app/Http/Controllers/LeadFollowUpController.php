<?php

namespace App\Http\Controllers;

use App\Enums\FollowUpStatus;
use App\Enums\FollowUpType;
use App\Helpers\ActivityLogHelper;
use App\Helpers\DateHelper;
use App\Http\Requests\BulkUpdateLeadFollowUpRequest;
use App\Models\FollowUp;
use App\Models\Lead;
use DateInterval;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeadFollowUpController extends Controller
{
    public function bulkEdit(
        Request $request,
        Lead $lead,
        $type = FollowUpType::EMAIL
    ) {
        $follow_ups = ($type === FollowUpType::CALL)
            ? ($lead
                ->call_follow_ups()
                ->where('sales_person_id', Auth::id())
                ->where('status', FollowUpStatus::PENDING)
                ->orderBy('send_reminder_at')
                ->get())
            : ($lead
                ->email_follow_ups()
                ->where('sales_person_id', Auth::id())
                ->where('status', FollowUpStatus::PENDING)
                ->orderBy('send_reminder_at')
                ->get());
        if ($type === FollowUpType::CALL) {
            $query = $lead->call_follow_ups();
        } else {
            $query = $lead->email_follow_ups();
        }
        $completed_follow_up_count = $query
            ->where('status', FollowUpStatus::COMPLETED)
            ->count();
        $latest_completed_follow_ups = $query
            ->where('status', FollowUpStatus::COMPLETED)
            ->orderByDesc('send_reminder_at')
            ->pluck('send_reminder_at');

        return view(
            $type === FollowUpType::CALL
                ? 'follow_ups.bulk-edit-call'
                : 'follow_ups.bulk-edit-email',
            compact(
                'lead',
                'follow_ups',
                'completed_follow_up_count',
                'latest_completed_follow_ups'
            )
        );
    }

    public function bulkUpdate(
        BulkUpdateLeadFollowUpRequest $request,
        Lead $lead,
        $type = FollowUpType::EMAIL
    ) {

        DB::beginTransaction();

        try {

            $valid = $request->validated();
            $keep = [];

            foreach ($valid['lead_follow_ups'] as $key => $data) {

                $updating = false;

                if (
                    isset($data['follow_up_id']) &&
                    !empty($data['follow_up_id'])
                ) {

                    $follow_up = FollowUp::query()
                        ->where('sales_person_id', Auth::id())
                        ->where('lead_id', $lead->id)
                        ->where('type', $type)
                        ->where('id',  $data['follow_up_id'])
                        ->firstOrFail();

                    $updating = true;

                    array_push($keep, $follow_up->id);

                    if (
                        $follow_up->status
                        ===
                        FollowUpStatus::COMPLETED
                    ) {
                        continue;
                    }
                } else {

                    $follow_up = new FollowUp;

                    $follow_up->sales_person_id
                        = Auth::id();
                    $follow_up->lead_id = $lead->id;
                    $follow_up->type = $type;
                }

                $data['follow_up_at'] =
                    date_create_from_format(
                        DateHelper::FOLLOW_UP_DATE,
                        $data['follow_up_date'] .
                            " " .
                            $data['follow_up_time'],
                        new DateTimeZone(
                            Auth::user()->timezone
                        )
                    )
                    ->setTimezone(new DateTimeZone('UTC'));

                if ($type == FollowUpType::CALL) {

                    $data['send_reminder_at'] =
                        date_create_from_format(
                            DateHelper::FOLLOW_UP_DATE,
                            $data['follow_up_date']
                                . " "
                                .  $data['follow_up_time'],
                            new DateTimeZone(
                                Auth::user()->timezone
                            )
                        )
                        ->setTimezone(
                            new DateTimeZone('UTC')
                        )
                        ->sub(
                            new DateInterval("PT1H")
                        );

                    $follow_up->sales_person_phone
                        = $data['sales_person_phone'];
                } else {

                    $data['send_reminder_at']
                        = $data['follow_up_at'];

                    $follow_up->bcc                 = $data['bcc'] ?? [];
                    $follow_up->content             = $data['content'];
                    $follow_up->subject             = $data['subject'];
                    $follow_up->to                  = $data['to'];
                    $follow_up->email_signature_id  = $data['email_signature_id'];
                    $follow_up->smtp_credential_id  = $data['smtp_credential_id'];
                }

                $follow_up->follow_up_at
                    = $data['follow_up_at'];
                $follow_up->send_reminder_at
                    = $data['send_reminder_at'];

                $follow_up->save();

                array_push($keep, $follow_up->id);

                ActivityLogHelper::log(
                    $updating
                        ? 'follow_ups.updated'
                        : 'follow_ups.created',
                    $updating
                        ? 'Follow up updated'
                        : 'Follow up created',
                    [
                        'data' => $data,
                    ],
                    $request,
                    Auth::user(),
                    $follow_up
                );
            }

            FollowUp::where('type', $type)
                ->where('sales_person_id', Auth::id())
                ->where('lead_id', $lead->id)
                ->where('status', FollowUpStatus::PENDING)
                ->whereNotIn('id', $keep)
                ->delete();

            DB::commit();
            return response()->json([
                'success' => true
            ], 201);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
                'error' => $th
            ], 500);
        }
    }
}
