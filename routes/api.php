<?php

use App\Http\Controllers\Api\ClientFeedbackController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\WiseWebhookController;
use App\Http\Controllers\Api\MailWebhookController;
use App\Http\Controllers\Api\InvoiceMailWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('get-in-touch', [LeadController::class, 'getInTouch']);

Route::post('client-feedback', [ClientFeedbackController::class, 'clientReviewFeedback']);
Route::post('feedback-token-check', [ClientFeedbackController::class, 'feedbackTokenCheck']);

Route::fallback(function () {
    abort(404);
});

Route::post('/webhook/wise', WiseWebhookController::class);
Route::any('/webhook/mail', [MailWebhookController::class,'mailStatus']);

// Invoice Mail Send
Route::post('/webhook/invoice/mail', [InvoiceMailWebhookController::class,'mailStatus']);
