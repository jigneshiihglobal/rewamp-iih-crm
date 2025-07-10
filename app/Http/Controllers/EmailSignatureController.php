<?php

namespace App\Http\Controllers;

use App\Models\EmailSignature;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use App\Helpers\EncryptionHelper;
use Carbon\Carbon;

class EmailSignatureController extends Controller
{
    public function preview(Request $request)
    {
        $valid = $request->validate([
            'workspace_id'  => 'required|exists:workspaces,id',
            'sign_name'  => 'required|string|min:2',
            'name'  => 'required|string|min:2',
            'position' => "required|string|min:2",
            'email' => 'required|email',
            'mobile_number' => 'required|array|min:1',
            'mobile_number.*' => 'required|string|min:8|max:20|regex:/^(\+)?([0-9]+(\s)?)+$/',
            'image_link' => 'nullable|url',
        ]);
        $valid['mobile_number'] = implode('|', $valid['mobile_number']);
        $email_signature = new EmailSignature($valid);
        $ws = Workspace::findOrFail($valid['workspace_id']);
        if($ws->slug === 'shalin-designs') {
            return view('emails.shalin-designs.invoices.user-email-signature', compact('email_signature'));
        }
        return view('emails.invoices.user-email-signature', compact('email_signature'));
    }

    public function signatureDelete(Request $request ,EmailSignature $emailSignature)
    {
        $date = Carbon::now();
        $id = $request->id;
        $data = EmailSignature::find($id);
        $data->update(['deleted_at'=>$date]);

    }
}
