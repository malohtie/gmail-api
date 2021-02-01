<?php


namespace App\Libs;


use App\Models\Account;
use Google_Client;

class Gmail
{
    protected Account $account;
    protected Google_Client $client;

    public function __construct(Account $account)
    {
        $this->account = $account;
        $this->client = new Google_Client();

    }

    public function createAuth()
    {

    }

    public function remove

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
