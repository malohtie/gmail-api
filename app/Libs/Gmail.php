<?php


namespace App\Libs;


use App\Models\Account;
use Google_Client;
use Google_Service_Gmail;
use Google_Service_Gmail_SendAs;
use Google_Service_Gmail_VacationSettings;

class Gmail
{
    protected Account $account;
    protected Google_Client $client;

    public function __construct(Account $account)
    {
        $this->account = $account;
        $this->client = new Google_Client([
            'client_secret' => $account->api->client_secret,
            'client_id' => $account->api->client_id,
            'redirect_uri' => url('accounts/callback'),
            'state' => self::base64UrlEncode(json_encode([
                'account' => $account->id,
            ])),
        ]);
        $this->client->setScopes([
            Google_Service_Gmail::GMAIL_SETTINGS_BASIC,
            Google_Service_Gmail::GMAIL_SETTINGS_SHARING,
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
        return $this->client->fetchAccessTokenWithAuthCode($code);
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

    public function setFrom(string $from): array
    {
        try {
            $this->reAuth();
            $service = new Google_Service_Gmail($this->client);
            /*$lists = $service->users_settings_sendAs->listUsersSettingsSendAs('me');
            dump($lists);
            $sendas = new Google_Service_Gmail_SendAs();
            $sendas->setDisplayName("ok");
            $sendas->setSendAsEmail("gmllsolution+ssss@gmail.com");
            $sendas->setIsDefault(true);
            $sendas->setTreatAsAlias(true);
            $sendas->setReplyToAddress("");
            $sendas->setSignature("");
            $sendas->setVerificationStatus("accepted");
            dump($sendas);
            $res = $service->users_settings_sendAs->create('me', $sendas);
            dd($res);*/
            $lists = $service->users_settings_sendAs->listUsersSettingsSendAs('me');
            $list = $lists->getSendAs()[0];
            $list->setDisplayName($from);
            $list->setIsDefault(true);
            $service->users_settings_sendAs->update('me', $this->account->email, $list);
            return ['status' => true, 'message' => 'OK'];
        } catch (\Exception $ex) {
            $error = json_decode($ex->getMessage(), true);
            //dd($error);
            if(isset($error['error']['message'])) {
                return ['status' => false, 'message' => $error['error']['message']];
            }
            return ['status' => false, 'message' => 'Error !'];
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
