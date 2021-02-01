<?php


namespace App\Libs;


use App\Models\Account;
use Google_Client;
use Google_Service_Gmail;

class Gmail
{
    protected Account $account;
    protected Google_Client $client;

    public function __construct(Account $account)
    {
        $this->account = $account;
        $this->client = new Google_Client([
            'client_secret' => config('gmail.client_secret'),
            'client_id' => config('gmail.client_id'),
            'redirect_uri' => url(config('gmail.redirect_url')),
            'state' => self::base64UrlEncode(json_encode([
                'account' => $account->id,
            ])),
        ]);
        $this->client->setScopes([
            Google_Service_Gmail::GMAIL_SETTINGS_BASIC,
            Google_Service_Gmail::GMAIL_MODIFY,
            Google_Service_Gmail::GMAIL_READONLY
        ]);
        $this->client->setAccessType(config('gmail.access_type'));
        $this->client->setApprovalPrompt(config('gmail.approval_prompt'));
    }

    public function createAuth(): string
    {
        return $this->client->createAuthUrl();
    }

    public function makeAuth(string $code)
    {
        $token = '';
        $data = $this->account->token;
        if (!empty($data) && !empty($data['access_token'])) {
            $this->client->setAccessToken($data);
            if ($this->client->isAccessTokenExpired()) {
                $token = $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
            }
        } else {
            $token = $this->client->fetchAccessTokenWithAuthCode($code);
        }
        return $token;
    }

    public function profil()
    {
        $service = new Google_Service_Gmail($this->client);
        return $service->users->getProfile('me');
    }

    public function disconnect(): bool
    {
        return $this->client->revokeToken();
    }

    /**
     * Safe Base64 Encode Url
     * @param $inputStr
     * @return string
     */
    public static function base64UrlEncode($inputStr)
    {
        return strtr(base64_encode($inputStr), '+/=', '-_,');
    }

    /**
     * Safe Base64 Decode Url
     * @param $inputStr
     * @return false|string
     */
    public static function base64UrlDecode($inputStr)
    {
        return base64_decode(strtr($inputStr, '-_,', '+/='));
    }

}
