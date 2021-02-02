<?php


namespace App\Libs;


use App\Models\Account;
use Google_Client;
use Google_Service_Gmail;
use Google_Service_Gmail_VacationSettings;

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

    public function makeAuth(string $code): array
    {
        $data = $this->account->token;
        if (!empty($data) && !empty($data['access_token'])) {
            $this->client->setAccessToken($data);
            if ($this->client->isAccessTokenExpired()) {
                $token = $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
            } else {
                $token = $this->account->token;
            }
        } else {
            $token = $this->client->fetchAccessTokenWithAuthCode($code);
        }
        return $token;
    }

    public function profile()
    {
        $service = new Google_Service_Gmail($this->client);
        return $service->users->getProfile('me');
    }

    private function reAuth()
    {
        $data = $this->account->token;
        $this->client->setAccessToken($data);
        if ($this->client->isAccessTokenExpired()) {
            $token = $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
            $this->account->token = $token;
            $this->account->save();
        }
    }

    public function setVacation(string $subject, string $body): bool
    {
        try {
            $this->reAuth();
            $vacation = new Google_Service_Gmail_VacationSettings();
            $vacation->setEnableAutoReply(true);
            $vacation->setResponseSubject($subject);
            $vacation->setResponseBodyHtml($body);
            $vacation->setStartTime(now()->getPreciseTimestamp(3));
            $vacation->setEndTime(now()->addYears(1)->getPreciseTimestamp(3));
            $service = new Google_Service_Gmail($this->client);
            $service->users_settings->updateVacation('me', $vacation);
            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function disconnect(): bool
    {
        return $this->client->revokeToken();
    }

    public static function base64UrlEncode($inputStr): string
    {
        return strtr(base64_encode($inputStr), '+/=', '-_,');
    }

    public static function base64UrlDecode($inputStr): string
    {
        return base64_decode(strtr($inputStr, '-_,', '+/='));
    }

}
