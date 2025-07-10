<?php

namespace App\Observers;

use App\Helpers\QuickBooksHelper;
use App\Models\Client;
use App\Models\QuickBooksToken;
use Illuminate\Support\Facades\Log;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\Customer;
use Illuminate\Support\Facades\DB;

class ClientObserver
{
    /**
     * Handle the Client "created" event.
     *
     * @param  \App\Models\Client  $client
     * @return void
     */
    public function created(Client $client)
    {
        try {
            $userId = auth()->id();
            QuickBooksHelper::refreshAccessToken($userId);

            $quickbooksToken = DB::table('quickbooks_tokens')->where('user_id', $userId)->first();

            $accessTokenKey = $quickbooksToken->access_token;
            $refreshTokenKey = $quickbooksToken->refresh_token;
            $QBORealmID = $quickbooksToken->realm_id;

            if (!$quickbooksToken) {
                Log::error("ClientObserver: No QuickBooks token found for user", ['user_id' => $userId]);
                return;
            }

            $dataService = DataService::Configure([
                'auth_mode'       => 'oauth2',
                'ClientID'        => config('services.quickbooks.client_id'),
                'ClientSecret'    => config('services.quickbooks.client_secret'),
                'accessTokenKey'  => $accessTokenKey,
                'refreshTokenKey' => $refreshTokenKey,
                'QBORealmID'      => $QBORealmID,
                'RedirectURI'     => config('services.quickbooks.redirect_uri'),
                'baseUrl'         => config('services.quickbooks.environment') === 'sandbox' ? 'Development' : 'Production',
            ]);

            $dataService->throwExceptionOnError(true);
            $dataService->setLogLocation(storage_path('logs/quickbooks'));

            $email = is_string($client->email) ? explode(',', $client->email)[0] : ($client->email ?? '');
            $email = trim($email);

            $customerData = [
                "FullyQualifiedName" => $client->name,
                "DisplayName"        => $client->name,
                "PrimaryEmailAddr"   => [
                    "Address" => $email ?? '',
                ],
                "PrimaryPhone" => [
                    "FreeFormNumber" => $client->phone ?? '',
                ],
                "BillAddr" => [
                    "Line1"      => $client->address_line_1 ?? '',
                    "City"       => $client->city ?? '',
                    "PostalCode" => $client->zip_code ?? '',
                    "Country"    => optional($client->country)->name ?? '',
                ],
                "CompanyName" => $client->name,
                "GivenName"   => $client->name,
            ];

            Log::info("ClientObserver: QuickBooks customer payload", $customerData);

            $customerObject = Customer::create($customerData);
            $resultingCustomerObj = $dataService->Add($customerObject);

            $customerId = is_array($resultingCustomerObj->Id)
                ? json_encode($resultingCustomerObj->Id)
                : (string) $resultingCustomerObj->Id;

            $client->qb_customer_id = $customerId;
            $client->saveQuietly();

        Log::info("ClientObserver: Customer created in QuickBooks with ID {$customerId} for client ID {$client->id}");

        } catch (\Exception $e) {
            Log::error("ClientObserver: QuickBooks customer creation failed", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }


    /**
     * Handle the Client "updated" event.
     *
     * @param  \App\Models\Client  $client
     * @return void
     */
    public function updated(Client $client)
    {
       try {
            $userId = auth()->id();

            // Refresh token
            QuickBooksHelper::refreshAccessToken($userId);

            $quickbooksToken = DB::table('quickbooks_tokens')->where('user_id', $userId)->first();
            
            $accessTokenKey = $quickbooksToken->access_token;
            $refreshTokenKey = $quickbooksToken->refresh_token;
            $QBORealmID = $quickbooksToken->realm_id;

            if (!$quickbooksToken) {
                Log::error("ClientObserver: No QuickBooks token found for user", ['user_id' => $userId]);
                return;
            }

            $dataService = DataService::Configure([
                'auth_mode'       => 'oauth2',
                'ClientID'        => config('services.quickbooks.client_id'),
                'ClientSecret'    => config('services.quickbooks.client_secret'),
                'accessTokenKey'  => $accessTokenKey,
                'refreshTokenKey' => $refreshTokenKey,
                'QBORealmID'      => $QBORealmID,
                'RedirectURI'     => config('services.quickbooks.redirect_uri'),
                'baseUrl'         => config('services.quickbooks.environment') === 'sandbox' ? 'Development' : 'Production',
            ]);

            $dataService->throwExceptionOnError(true);
            $dataService->setLogLocation(storage_path('logs/quickbooks'));

            // CRITICAL FIX: Ensure internal context is not null
            if (!$dataService->getServiceContext()) {
                Log::error('QuickBooks SDK initialization failed: ServiceContext is null.', [
                    'user_id' => $userId,
                    'realm_id' => $quickbooksToken->realm_id,
                ]);
                return;
            }

            // Prepare customer data
            $email = is_string($client->email) ? explode(',', $client->email)[0] : ($client->email ?? '');
            $email = trim($email);

            $customerData = [
                "FullyQualifiedName" => $client->name,
                "DisplayName"        => $client->name,
                "PrimaryEmailAddr"   => ["Address" => $email],
                "PrimaryPhone"       => ["FreeFormNumber" => $client->phone ?? ''],
                "BillAddr"           => [
                    "Line1"      => $client->address_line_1 ?? '',
                    "City"       => $client->city ?? '',
                    "PostalCode" => $client->zip_code ?? '',
                    "Country"    => optional($client->country)->name ?? '',
                ],
                "CompanyName"        => $client->name,
                "GivenName"          => $client->name,
            ];

            Log::info("ClientObserver: QuickBooks customer payload", $customerData);

            if (!empty($client->qb_customer_id)) {
                $existingCustomer = $dataService->FindById('Customer', $client->qb_customer_id);
                if ($existingCustomer) {
                    $patchedCustomer = Customer::update($existingCustomer, $customerData);
                    $updatedCustomer = $dataService->Update($patchedCustomer);

                    Log::info("ClientObserver: Customer updated in QuickBooks", [
                        'qb_id' => $updatedCustomer->Id
                    ]);
                    return;
                } else {
                    Log::warning("ClientObserver: Customer ID {$client->qb_customer_id} not found in QuickBooks. Creating new.");
                }
            }

            $newCustomer = Customer::create($customerData);
            $result = $dataService->Add($newCustomer);

            $client->qb_customer_id = (string) $result->Id;
            $client->saveQuietly();

            Log::info("ClientObserver: Customer created in QuickBooks", ['qb_id' => $result->Id]);

        } catch (\Exception $e) {
            dd( $e->getTraceAsString());
            Log::error("ClientObserver: QuickBooks sync failed", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }


    /**
     * Handle the Client "deleted" event.
     *
     * @param  \App\Models\Client  $client
     * @return void
     */
    public function deleted(Client $client)
    {
        try {
            if (!$client->qb_customer_id) return;

            $dataService = $this->getDataService();
            $qbCustomer = $dataService->FindById('Customer', $client->qb_customer_id);
            if ($qbCustomer) {
                $dataService->Delete($qbCustomer);
            }
        } catch (\Exception $e) {
            Log::error("QuickBooks customer delete failed: " . $e->getMessage());
        }
    }

    /**
     * Handle the Client "restored" event.
     *
     * @param  \App\Models\Client  $client
     * @return void
     */
    public function restored(Client $client)
    {
        //
    }

    /**
     * Handle the Client "force deleted" event.
     *
     * @param  \App\Models\Client  $client
     * @return void
     */
    public function forceDeleted(Client $client)
    {
        //
    }

    private function getDataService(): DataService
    {
        $quick_books_token = QuickBooksToken::where('user_id',auth()->id())->first();

        $dataService = DataService::Configure([
            'auth_mode' => 'oauth2',
            'ClientID' => config('services.quickbooks.client_id'),
            'ClientSecret' => config('services.quickbooks.client_secret'),
            'accessTokenKey' => $quick_books_token->access_token,
            'refreshTokenKey' => $quick_books_token->refresh_token,
            'QBORealmID' => $quick_books_token->realm_id,
            'RedirectURI' => config('services.quickbooks.redirect_uri'),
            'scope' => 'com.intuit.quickbooks.accounting',
            'baseUrl' => config('services.quickbooks.environment') === 'sandbox' ? 'Development' : 'Production'
        ]);
        return $dataService;
    }
}
