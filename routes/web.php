<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ContactedLeadMailController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\SetPasswordController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\EmailSignatureController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseMarketingController;
use App\Http\Controllers\ExpenseMarketingNoteController;
use App\Http\Controllers\ExpenseNoteController;
use App\Http\Controllers\ExpenseTypeExpenseSubTypeController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeadAttachmentController;
use App\Http\Controllers\MarketingInvoiceController;
use App\Http\Controllers\PaymentReceivedController;
use App\Http\Controllers\SalesInvoiceController;
use App\Http\Controllers\StripeshalinWebhookController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\SalesUserInvoiceController;
use App\Http\Controllers\WiseShalinDesignsWebhookController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoicesNoteController;
use App\Http\Controllers\NoteReminderController;
use App\Http\Controllers\InvoiceCreditNoteController;
use App\Http\Controllers\InvoiceHomeController;
use App\Http\Controllers\InvoicePaymentController;
use App\Http\Controllers\InvoicePaymentLinkController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadFollowUpController;
use App\Http\Controllers\LeadNoteController;
use App\Http\Controllers\LeadSourceController;
use App\Http\Controllers\LeadStatusController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\WiseWebhookController;
use App\Http\Controllers\WiseshalinWebhookController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/check-session', function () {
    if (Auth::check()) {
        return response()->json(['status' => 'authenticated'], 200);
    } else {
        return response()->json(['status' => 'unauthenticated'], 401);
    }
})->name('check_session');

Auth::routes([
    "register" => false,
    "password.confirm" => true,
    "password.email" => true,
    "password.request" => true,
    "password.reset" => true,
    "password.update" => true,
]);

Route::middleware(['guest'])->group(function () {
    Route::get('/set-password', [SetPasswordController::class, 'showSetPasswordForm'])->name('set-password');
    Route::post('/set-password', [SetPasswordController::class, 'setPassword'])->name('set-password.submit');
});

Route::middleware(['auth',  'active'])->group(function () {
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout.public');
});

Route::middleware(['auth', 'role:User|Admin|Superadmin|Marketing', 'check_location', 'active'])->group(function () {

    Route::get('/dashboard/review_user', [HomeController::class, 'getUserReviewData'])->middleware(['role:Admin|Superadmin|Marketing|User', 'workspace:iih-global,shalin-designs'])->name('dashboard.review_user');
    Route::get('/dashboard/won_lead', [HomeController::class, 'getWonLeadData'])->middleware(['role:Admin|Superadmin|Marketing|User', 'workspace:iih-global,shalin-designs'])->name('dashboard.won_lead');
    Route::get('/dashboard/graph-data', [HomeController::class, 'getGraphData'])->middleware(['role:Admin|Superadmin|Marketing', 'workspace:iih-global,shalin-designs'])->name('dashboard.graph-data');
    Route::get('/dashboard', [HomeController::class, 'index'])->middleware(['role:Admin|Superadmin|Marketing|User', 'workspace:iih-global,shalin-designs'])->name('dashboard');
    Route::get('/workspaces/change/{workspace}', [WorkspaceController::class, 'changeWorkspace'])->name('workspaces.change');
    Route::get('/won-lead', [HomeController::class, 'wonLead'])->name('won-lead');
    Route::get('/kudos-list', [HomeController::class, 'kudosList'])->name('kudos-list');

    Route::resource('follow_ups', FollowUpController::class)
        ->except([
            'show',
            'create',
        ])
        ->middleware([
            'workspace:iih-global,shalin-designs',
            'role:User|Admin',
        ]);
    // Leads
    Route::group(
        [
            "as" => "leads.",
            "prefix" => "leads",
            "middleware" => "workspace:iih-global,shalin-designs"
        ],
        function () {
            // follow ups
            Route::group([
                'as' => 'follow-ups.',
                'prefix' => '/{lead}/follow-ups/',
                'middleware' => ['role:User|Admin', 'workspace:iih-global,shalin-designs']
            ], function () {
                Route::get("/bulk-edit/{type?}", [LeadFollowUpController::class, 'bulkEdit'])->name('bulk-edit');
                Route::post("/bulk-update/{type?}", [LeadFollowUpController::class, 'bulkUpdate'])->name('bulk-update');
            });

            Route::post("/export-filtered", [LeadController::class, 'exportFiltered'])->name("export-filtered");
            Route::get("/", [LeadController::class, 'index'])->name("index");
            Route::post("/", [LeadController::class, 'store'])->name("store");
            Route::get("/{lead}", [LeadController::class, 'show'])->name("show");
            Route::get("/edit/{lead}", [LeadController::class, 'edit'])->name("edit");
            Route::put("/{lead}", [LeadController::class, 'update'])->name("update");
            Route::delete("/{lead}", [LeadController::class, 'destroy'])->name("destroy");
            Route::put("/restore/{lead}", [LeadController::class, 'restore'])->middleware('role:Admin|Superadmin|Marketing')->name("restore");
            Route::delete('/{lead}/force-delete', [LeadController::class, 'forceDelete'])->middleware('role:Admin|Superadmin|Marketing')->name('force-delete');
            Route::get('/{lead}/follow-up-details', [LeadController::class,  'followUpDetails'])
                ->middleware([
                    'workspace:iih-global,shalin-designs',
                ])
                ->name('follow-up-details');

            Route::post('/marketing-mail-reminder-status', [LeadController::class, 'marketingMailReminderStatus'])->name('marketing_mail_reminder_status');

            // Lead notes
            Route::group([
                "as" => "notes.",
                "prefix" => "/{lead}/notes"
            ], function () {

                Route::get("/", [LeadNoteController::class, 'index'])->name('index');
                Route::post("/", [LeadNoteController::class, 'store'])->name('store');
                Route::put("/{leadNote}", [LeadNoteController::class, 'update'])->name('update');
                Route::delete("/{leadNote}", [LeadNoteController::class, 'destroy'])->name('destroy');
            });

            // Attachments
            Route::group([
                "as" => "attachments.",
                "prefix" => "/{lead}/attachments"
            ], function () {
                Route::delete("/{attachment}", [LeadAttachmentController::class, 'destroy'])->name('destroy');
            });
        }
    );

    Route::middleware(['workspace:iih-global,shalin-designs'])->group(function () {

        // Notifications
        Route::group([
            'as' => 'notifications.',
            'prefix' => 'notifications'
        ], function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::get('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-as-read');
        });

        // User Profile
        Route::group([
            'as' => 'profile.',
            'prefix' => '/profile'
        ], function () {
            Route::get('/', [ProfileController::class, 'index'])->name('index');
            Route::put('/update/{user?}', [ProfileController::class, 'update'])->name('update');
            Route::post('/picture/{user?}', [ProfileController::class, 'updatePicture'])->name('picture');
            Route::delete('/picture/{user?}', [ProfileController::class, 'removePicture'])->name('picture.remove');
            Route::put('/update-password/{user?}', [ProfileController::class, 'updatePassword'])->name('update-password');
            Route::put('/update-workspace-access/{user?}', [ProfileController::class, 'updateWorkspaceAccess'])->middleware('role:Superadmin')->name('update-workspace-access');
            Route::put('/update-group/{user?}', [ProfileController::class, 'updateGroup'])->name('update-group');
            Route::put('/signature/{user?}', [ProfileController::class,  'updateSignature'])->name('signature.update');
            Route::put('/smtp/{user?}', [ProfileController::class,  'updateSmtp'])->name('smtp.update');
        });

        // Users
        Route::group(
            [
                "as" => "users.",
                "prefix" => "users",
                "middleware" => ['role:Admin|Superadmin|Marketing', 'workspace:iih-global'],
            ],
            function () {
                Route::get("/", [UserController::class, 'index'])->name("index");
                Route::get("/{user}", [UserController::class, 'show'])->name("show");
                Route::post("/", [UserController::class, 'store'])->name("store");
                Route::delete("/force-destroy/{user}", [UserController::class, 'forceDestroy'])->name("force.destroy");
                Route::delete("/{user}", [UserController::class, 'destroy'])->name("destroy");
                Route::put("/restore/{user}", [UserController::class, 'restore'])->name("restore");
                Route::post("/review/{user}", [UserController::class, 'reviewStore'])->name("review-store");
                Route::get("/user_review/{user}", [UserController::class, 'userReview'])->name("user_review");
            }
        );

        // Lead sources
        Route::middleware(['workspace:iih-global'])->group(function () {
            Route::group([
                'as' => 'lead_sources.',
                'prefix' => '/lead_sources'
            ], function () {
                Route::put('/restore/{lead_source}', [LeadSourceController::class, 'restore'])->name('restore')->middleware('role:Admin|Superadmin|Marketing');
                Route::delete('/force-delete/{lead_source}', [LeadSourceController::class, 'forceDestroy'])->name('force-delete')->middleware('role:Admin|Superadmin|Marketing');
            });
            Route::resource('lead_sources', LeadSourceController::class)->middleware('role:Admin|Superadmin|Marketing');
        });

        // Lead statuses
        Route::middleware(['workspace:iih-global'])->group(function () {
            Route::group([
                'as' => 'lead_statuses.',
                'prefix' => '/lead_statuses'
            ], function () {
                Route::put('/restore/{lead_status}', [LeadStatusController::class, 'restore'])->name('restore')->middleware('role:Admin|Superadmin|Marketing');
                Route::delete('/force-delete/{lead_status}', [LeadStatusController::class, 'forceDestroy'])->name('force-delete')->middleware('role:Admin|Superadmin|Marketing');
            });
            Route::resource('lead_statuses', LeadStatusController::class)->middleware('role:Admin|Superadmin|Marketing');
        });

        // Activities
        Route::group(
            [
                "as" => "activities.",
                "prefix" => "activities"
            ],
            function () {
                Route::get("/", [ActivityController::class, 'index'])->name("index");
            }
        );

        // contacted lead
        Route::group(
            [
                "as" => "contacted-lead.",
                "prefix" => "contacted-lead"
            ],
            function () {
                Route::get("/", [ContactedLeadMailController::class, 'index'])->name("index");
                Route::get("/mail-content", [ContactedLeadMailController::class, 'show'])->name("show");
            }
        );

        // Invoices
        Route::group(
            [
                "as" => "invoices.",
                "prefix" => "invoices",
                "middleware" => ['role:Superadmin', 'workspace:iih-global,shalin-designs']
            ],
            function () {

                // Subscription invoices related routes
                Route::group(
                    [
                        'as' => 'subscription.',
                        'prefix' => 'subscription',
                    ],
                    function () {
                        Route::get("/create", [InvoiceController::class, 'createSub'])->name("create");
                        Route::post("/store", [InvoiceController::class, 'storeSub'])->name("store");
                        Route::get('/{invoice}/edit', [InvoiceController::class, 'editSub'])->name('edit');
                        Route::put('/{invoice}/update', [InvoiceController::class, 'updateSub'])->name('update');
                        Route::get('/{invoice}', [InvoiceController::class, 'showSub'])->name('show');
                    }
                );

                // Notes route
                Route::get("/invoice_notes", [InvoicesNoteController::class, 'index'])->name('invoice_notes.index');
                Route::post("/invoice_notes", [InvoicesNoteController::class, 'store'])->name('invoice_notes.store');
                Route::put("/{invoiceNote}", [InvoicesNoteController::class, 'update'])->name('invoice_notes.update');
                Route::delete("/{invoiceNote}", [InvoicesNoteController::class, 'destroy'])->name('invoice_notes.destroy');

                // Note Reminders route
                Route::get("/note_reminders", [NoteReminderController::class, 'index'])->name('note_reminders.index');
                Route::post("/note_reminders", [NoteReminderController::class, 'store'])->name('note_reminders.store');
                Route::get("/{NoteReminder}/note_reminders", [NoteReminderController::class, 'show'])->name('note_reminders.show');
                Route::put("/{NoteReminder}/note_reminders", [NoteReminderController::class, 'update'])->name('note_reminders.update');
                Route::delete("/{NoteReminder}/note_reminders", [NoteReminderController::class, 'destroy'])->name('note_reminders.destroy');

                // one-off invoices and common invoice routes
                Route::post('/export', [InvoiceController::class, 'export'])->name('export');
                Route::post('/bank-export', [InvoiceController::class, 'bankExport'])->name('bank_export');
                Route::post('/payment_receipt_export', [InvoiceController::class, 'paymentReceiptExport'])->name('payment_receipt_export');
                // Route::get('/dashboard/graph-data', [InvoiceHomeController::class, 'getGraphData'])->name('dashboard.graph-data');
                Route::get('/dashboard/graph-data', [InvoiceHomeController::class, 'getGraphDataV2'])->name('dashboard.graph-data');
                Route::get('/dashboard/vat-graph-data', [InvoiceHomeController::class, 'getVatGraphData'])->name('dashboard.vat-graph-data');
                Route::get('/dashboard', [InvoiceHomeController::class, 'index'])->name('dashboard');

                Route::get("/sales-statistics", [InvoiceController::class, 'getSalesStatistics'])->name("sales-statistics");
                Route::get("/", [InvoiceController::class, 'index'])->name("index");
                Route::get("/create-one-off", [InvoiceController::class, 'createOneOff'])->name("one-off.create");
                Route::post("/store-one-off", [InvoiceController::class, 'storeOneOff'])->name("one-off.store");
                Route::get('/{invoice}/send-mail', [InvoiceController::class, 'getMailContent'])->name('get-mail-content');
                Route::post('/{invoice}/send-mail', [InvoiceController::class, 'sendMailToClient'])->name('send-mail');
                Route::get('/{invoice}/preview', [InvoiceController::class, 'preview'])->name('preview');
                Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])->name('edit');
                Route::put('/{invoice}/update', [InvoiceController::class, 'update'])->name('update');
                Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
                Route::delete('/{invoice}/cancel', [InvoiceController::class, 'cancelInvoice'])->name('cancelled');

                Route::get('/{invoice}/send-receipt-mail', [InvoiceController::class, 'getReceiptMailContent'])->name('get-receipt-mail-content');
                Route::post('/{invoice}/send-receipt-mail', [InvoiceController::class, 'sendReceiptMail'])->name('send-receipt-mail');

                Route::post('/payment-reminder', [InvoiceController::class, 'paymentReminder'])->name('payment_reminder');
                Route::put("/{invoice}/restore", [InvoiceController::class, 'restore'])->name("restore");

                // Invoice Payments
                Route::group(
                    [
                        "as" => "payments.",
                        "prefix" => "/invoices/{invoice}/",
                    ],
                    function () {
                        Route::post('/', [InvoicePaymentController::class, 'store'])->name('store');
                        Route::get("/last-5-payments", [InvoicePaymentController::class, 'last5Payments'])->name('last-5-payments');
                        Route::put("/paymentUpdate", [InvoicePaymentController::class, 'paymentUpdate'])->name('paymentUpdate');
                        Route::delete("/paymentDestroy", [InvoicePaymentController::class, 'paymentDestroy'])->name('paymentDestroy');
                        Route::post('/link', [InvoicePaymentLinkController::class, 'store'])->name('link.store');
                        Route::get('/link', [InvoicePaymentLinkController::class, 'show'])->name('link.show');
                    }
                );
            }
        );

        // User(Sales Person) Invoices
        Route::group(
            [
                "as" => "sales_invoices.",
                "prefix" => "sales_invoices",
                "middleware" => [
                    'role:User',
                    'workspace:iih-global',
                    'salesInvoice.access'
                ]
            ],
            function () {
                Route::get("/", [SalesUserInvoiceController::class, 'index'])->name("index");
                Route::get('/create', [SalesUserInvoiceController::class, 'create'])->name('create');
                Route::post('/store', [SalesUserInvoiceController::class, 'store'])->name('store');
                Route::get('/show/{user_invoice}', [SalesUserInvoiceController::class, 'show'])->name('show');
                Route::get('/edit/{user_invoice}', [SalesUserInvoiceController::class, 'edit'])->name('edit');
                Route::put('/update/{user_invoice}', [SalesUserInvoiceController::class, 'update'])->name('update');
                Route::delete('/delete/{user_invoice}', [SalesUserInvoiceController::class, 'destroy'])->name('destroy');

                Route::get('/{user_invoice}/send-mail', [SalesUserInvoiceController::class, 'userInvoiceGetMailContent'])->name('user-invoice-get-mail-content');
                Route::post('/{user_invoice}/send-mail', [SalesUserInvoiceController::class, 'sendMailToAdmin'])->name('send-mail-to-admin');
            }
        );

        // Invoice Credit Notes
        Route::middleware(['role:Superadmin', 'workspace:iih-global,shalin-designs'])->group(function () {
            Route::get('/credit_notes/{credit_note}/preview', [InvoiceCreditNoteController::class, 'preview'])->name('credit_notes.preview');
            Route::resource('invoices.credit_notes', InvoiceCreditNoteController::class)
                ->except(['index'])
                ->shallow(true);

            // Payment Received & Invoice Link
            Route::get("/payment-received", [PaymentReceivedController::class, 'index'])->name("payment_detail_index");
            Route::get("/invoices-dropdown", [PaymentReceivedController::class, 'invoicesDropdown'])->name("payment_invoices_dropdown");
            Route::post("/payment-invoices-link", [PaymentReceivedController::class, 'paymentInvoicesLink'])->name("payment_invoices_link");
        });

        Route::middleware(['role:Superadmin', 'workspace:iih-global'])->group(function () {
            // Sales Invoice
            Route::get("/admin-sales-invoice", [SalesInvoiceController::class, 'index'])->name("sales_invoice_index");
            Route::get("/admin-sales-invoice-show/{sales_invoice}", [SalesInvoiceController::class, 'show'])->name("sales_invoice_show");
            Route::get("/admin-sales-invoice-create/{sales_invoice}", [SalesInvoiceController::class, 'edit'])->name("sales_invoice_create");
            Route::post("/admin-sales-invoice-store-one-off", [SalesInvoiceController::class, 'storeOneOff'])->name("sales_invoice_store_one_off");
            Route::post("/admin-sales-invoice-store-sub", [SalesInvoiceController::class, 'storeSub'])->name("sales_invoice_store_sub");
            Route::delete("/admin-sales-invoice-delete/{sales_invoice}", [SalesInvoiceController::class, 'destroy'])->name("sales_invoice_destroy");                
        });
                    

        // Clients
        Route::group(
            [
                "as" => "clients.",
                "prefix" => "clients",
                "middleware" => ['role:Superadmin|User', 'workspace:iih-global,shalin-designs']
            ],
            function () {
                Route::get("/{client}/preferred-sales-person", [ClientController::class, 'preferredSalesPerson'])->name("preferred-sales-person");
                Route::get("/{client}", [ClientController::class, 'show'])->name("show");
                Route::get("/", [ClientController::class, 'index'])->name("index");
                Route::post("/", [ClientController::class, 'store'])->name("store");
                Route::put("/{client}", [ClientController::class, 'update'])->name("update");
                Route::put("/{client}/restore", [ClientController::class, 'restore'])->name("restore");
                Route::delete("/{client}", [ClientController::class, 'destroy'])->name("destroy");

                Route::post('/payment-reminder', [ClientController::class, 'paymentReminder'])->name('client_payment_reminder');

                Route::get('/{client}/review-send-mail', [ClientController::class, 'getMailContent'])->name('review_get_mail_content');
                Route::post('/{client}/review-send-mail', [ClientController::class, 'sendMailToClient'])->name('review_send_mail');
                Route::get('/{client}/review-email-history', [ClientController::class, 'getReviewEmailHistory'])->name('review_email_history');
                

                Route::post('/save-selected-projects', [ClientController::class, 'saveSelectedProjects'])->name('save_selected_projects');
                Route::post("/export-filtered", [ClientController::class, 'exportFiltered'])->name("export-filtered");
                /*Route::get("/review", [ClientController::class, 'clientReview'])->name("client_review");*/
            }
        );

        // Email Signatures
        Route::post("email_signatures/preview", [EmailSignatureController::class, 'preview'])->name('email_signatures.preview');
        Route::delete("email_signatures/delete", [EmailSignatureController::class, 'signatureDelete'])->name('email_signatures.delete');
        Route::delete('smtp/delete', [ProfileController::class,  'smtpDelete'])->name('smtp.delete');
    });

    Route::group(
        [
            'as' => 'system-settings.',
            'prefix' => 'system-settings',
            'middleware' => ['workspace:iih-global', 'role:Superadmin'],
        ],
        function () {
            Route::get('/', [SettingsController::class, 'edit'])->name('edit');
            Route::put('/', [SettingsController::class, 'update'])->name('update');
        }
    );

    Route::middleware([ 'workspace:iih-global,shalin-designs', 'role:Superadmin'])->group(function () {

        //Client Expense notes
        Route::group([
            "as" => "expenses.",
            "prefix" => "expenses/{any_expense}"
        ], function () {
            Route::apiResource('expense_notes', ExpenseNoteController::class)->except(['show']);
        });

        /* Client Expenses route */
        Route::get('expenses/{any_expense}/copy', [ExpenseController::class, 'copy'])->name('expenses.copy');
        Route::put('expenses/{deleted_expense}/restore', [ExpenseController::class, 'restore'])->name('expenses.restore');
        Route::get('expenses/create-many', [ExpenseController::class, 'createMany'])->name('expenses.create-many');
        Route::post('expenses/store-many', [ExpenseController::class, 'storeMany'])->name('expenses.store-many');
        Route::resource('expenses', ExpenseController::class)->except(['create']);

        Route::resource('expense_types.expense_sub_types', ExpenseTypeExpenseSubTypeController::class)
            ->only(['index'])
            ->middleware([
                'workspace:iih-global',
                'role:Superadmin'
            ]);

        /* Marketing Expenses route */
        Route::get('marketing/expenses', [ExpenseMarketingController::class, 'index'])->name('marketing.expenses.index');
        Route::post('marketing/expenses/store', [ExpenseMarketingController::class, 'store'])->name('marketing.expenses.store');
        Route::get('marketing/expenses/show/{any_expense_id}', [ExpenseMarketingController::class, 'show'])->name('marketing.expenses.show');
        Route::get('marketing/expenses/edit/{any_expense_id}', [ExpenseMarketingController::class, 'edit'])->name('marketing.expenses.edit');
        Route::post('marketing/expenses/update/{any_expense_id}', [ExpenseMarketingController::class, 'update'])->name('marketing.expenses.update');
        Route::delete('marketing/expenses/destroy/{any_expense_id}', [ExpenseMarketingController::class, 'destroy'])->name('marketing.expenses.destroy');

        Route::get('marketing/expenses/{any_expense_id}/copy', [ExpenseMarketingController::class, 'marketingCopy'])->name('marketing.expenses.copy');
        Route::put('marketing/expenses/{any_expense_id}/restore', [ExpenseMarketingController::class, 'marketingRestore'])->name('marketing.expenses.restore');
        Route::get('marketing/expenses/create-many', [ExpenseMarketingController::class, 'marketingCreateMany'])->name('marketing.expenses.create-many');
        Route::post('marketing/expenses/store-many', [ExpenseMarketingController::class, 'marketingStoreMany'])->name('marketing.expenses.store-many');

        Route::get("/marketing-rate-currency", [ExpenseMarketingController::class, 'getMarketingRateCurrency'])->name("marketing-rate-currency");

        /* Marketing Expenses Notes route */
        Route::group([
            "as" => "marketing.",
            "prefix" => "marketing/{any_expense_id}"
        ], function () {
            Route::apiResource('marketing_expense_notes', ExpenseMarketingNoteController::class)->except(['show']);
        });


    });

    /*Route::get('wise_payment',  [App\Http\Controllers\Api\WiseWebhookController::class, 'wisePaymentSend'])->name('wise_payment');*/
});

// IIH-CRM payment webhook url
Route::post('stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);
Route::post('wise/webhook', [WiseWebhookController::class, 'handleWebhook']);

// shalin Designs payment webhook url
Route::post('stripe/shalin-webhook', [StripeshalinWebhookController::class, 'handleWebhook']);
Route::post('wise/shalin-designs-webhook', [WiseshalinWebhookController::class, 'handleWebhook']);