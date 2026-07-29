<?php namespace Stheme\Turnstile\Classes;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Stheme\Turnstile\Models\Settings;
use Backend\Classes\AuthManager;

class TurnstileVerifier
{
    /**
     * Endpoint for Cloudflare Turnstile token verification
     */
    protected const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * Verifies Turnstile token against Cloudflare API
     *
     * @param string|null $token Response token (cf-turnstile-response)
     * @param string|null $remoteIp Client IP address
     * @return bool
     */
    public static function verify(?string $token, ?string $remoteIp = null): bool
    {
        // If Turnstile is globally disabled, consider verification successful
        if (!Settings::get('enabled', true)) {
            return true;
        }

        // Bypass for logged-in backend administrators
        if (Settings::get('bypass_admins', false) && class_exists(AuthManager::class) && AuthManager::instance()->check()) {
            return true;
        }

        $ip = $remoteIp ?: request()->ip();

        // Check IP Whitelist
        if (self::isIpWhitelisted($ip)) {
            return true;
        }

        $secretKey = Settings::get('secret_key');
        if (empty($secretKey)) {
            Log::warning('Cloudflare Turnstile: Secret key is not set in backend settings.');
            return true; // Avoid locking out forms when key is missing
        }

        if (empty($token)) {
            return false;
        }

        try {
            $response = Http::asForm()->post(self::VERIFY_URL, [
                'secret'   => $secretKey,
                'response' => $token,
                'remoteip' => $ip,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['success']) && $data['success'] === true) {
                    return true;
                }

                $errorCodes = isset($data['error-codes']) && is_array($data['error-codes'])
                    ? implode(', ', $data['error-codes'])
                    : 'unknown error';

                Log::error("Cloudflare Turnstile verification rejected for IP {$ip}. Error codes: {$errorCodes}");
                return false;
            }

            Log::error('Cloudflare Turnstile API HTTP error: Status ' . $response->status());
            return false;
        } catch (\Throwable $e) {
            Log::error('Cloudflare Turnstile verification exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Checks if given IP address is in the whitelisted IPs setting
     */
    protected static function isIpWhitelisted(string $ip): bool
    {
        $rawWhitelist = Settings::get('whitelisted_ips', '');
        if (empty(trim($rawWhitelist))) {
            return false;
        }

        $ips = preg_split('/[\r\n,]+/', $rawWhitelist);
        foreach ($ips as $whitelistedIp) {
            $whitelistedIp = trim($whitelistedIp);
            if ($whitelistedIp !== '' && $whitelistedIp === $ip) {
                return true;
            }
        }

        return false;
    }
}
