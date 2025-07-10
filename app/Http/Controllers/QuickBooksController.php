<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;
use App\Models\QuickBooksToken;

class QuickBooksController extends Controller
{
    public function connect()
    {
        $dataService = DataService::Configure([
            'auth_mode' => 'oauth2',
            'ClientID' => config('services.quickbooks.client_id'),
            'ClientSecret' => config('services.quickbooks.client_secret'),
            'RedirectURI' => config('services.quickbooks.redirect_uri'),
            'scope' => 'com.intuit.quickbooks.accounting',
            'baseUrl' => config('services.quickbooks.environment') === 'sandbox' ? 'Development' : 'Production'
        ]);
        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
        $authUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();

        return redirect($authUrl);
    }

    public function callback(Request $request)
    {
        $dataService = DataService::Configure([
            'auth_mode' => 'oauth2',
            'ClientID' => config('services.quickbooks.client_id'),
            'ClientSecret' => config('services.quickbooks.client_secret'),
            'RedirectURI' => config('services.quickbooks.redirect_uri'),
            'scope' => 'com.intuit.quickbooks.accounting',
            'baseUrl' => config('services.quickbooks.environment') === 'sandbox' ? 'Development' : 'Production'
        ]);

        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();

        $accessToken = $OAuth2LoginHelper->getAccessToken($request->query('code'));

         if (!$accessToken || !$accessToken->getAccessToken()) {
            throw new \Exception('Access token not received from QuickBooks.');
        }

        // Optional: Set the token in dataService
        $dataService->updateOAuth2Token($accessToken);
        
        QuickBooksToken::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'realm_id' => $request->query('realmId'),
                'access_token' => $accessToken->getAccessToken(),
                'refresh_token' => $accessToken->getRefreshToken(),
            ]
        );

        return redirect('/')->with('success', 'QuickBooks connected successfully.');
    }

}
