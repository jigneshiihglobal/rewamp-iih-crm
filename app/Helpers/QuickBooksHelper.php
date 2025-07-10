<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\QuickBooksToken;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;

class QuickBooksHelper
{
    public static function refreshAccessToken($userId = null)
    {   
        $client_id     = config('services.quickbooks.client_id');
        $client_secret = config('services.quickbooks.client_secret');
        $redirect_uri  = config('services.quickbooks.redirect_uri');

        // Fetch token for current user
        
        $token = QuickBooksToken::where('user_id', Auth::id())->first();

        if (!$token || !$token->refresh_token) {
            return response()->json(['error' => 'No QuickBooks refresh token found.'], 404);
        }

        try {
            $oauth2LoginHelper = new OAuth2LoginHelper(
                $client_id,
                $client_secret,
                $redirect_uri
            );

            // 🔁 Refresh the token
            $accessTokenObj = $oauth2LoginHelper->refreshAccessTokenWithRefreshToken($token->refresh_token);

            // Save updated token values
            $token->update([
                'access_token' => $accessTokenObj->getAccessToken(),
                'refresh_token' => $accessTokenObj->getRefreshToken(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('QuickBooks token refresh failed', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
