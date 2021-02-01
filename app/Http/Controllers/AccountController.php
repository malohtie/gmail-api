<?php

namespace App\Http\Controllers;

use App\Libs\Gmail;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /*
     *
     */
    public function index(Request $request)
    {
        if($request->ajax()) {
            return response()->json(['data' => Account::all()]);
        }
        return view('accounts');
    }

    public function add(Request $request)
    {
        $request->validate([
            'accounts' => 'required|array'
        ]);

        $nb = 0;
        foreach ($request->accounts as $account) {
            $email = strtolower($account);
            if(!Account::where('email', $email)->exists()) {
                $res = Account::create([
                    'email' => $email,
                    'is_active' => true
                ]);
                $res && $nb++;
            }
        }

        return response()->json([
            'status' => true,
            'nb' => $nb
        ]);
    }

    /**
     * Request Auth Gmail Account
     * @param Account $account
     * @param Request $request
     */
    public function auth(Account $account, Request  $request)
    {
        if($account->is_active) {
            $url = (new Gmail($account))->createAuth();
            return response()->redirectTo($url);
        }
        return response()->json(['status' => false , 'message' => 'account not active']);
    }

    /**
     * Save Auth Account
     * @param Request $request
     */
    public function callbackAuth(Request $request)
    {
        $result = false;
        $code = $request->code;
        $state = $request->state;
        if ($code && $state) {
            $state = Gmail::base64UrlDecode($state);
            $state = json_decode($state, true);
            if (!empty($state['account']) && $account = Account::find($state['account'])) {
                $gmail = new Gmail($account);
                $token = $gmail->makeAuth($code);
                if(isset($token['error'])) {
                    return response()->json(['status' => false, 'message' => 'could not generate token']);
                }
                $email = $gmail->profil();
                if ($email->emailAddress == $account->email) {
                    $account->token = $token;
                    $account->save();
                    return response()->json(['status' => true, 'message' => 'Token Generated Successfully']);
                }
                $gmail->disconnect();
                return response()->json(['status' => false, 'message' => 'Email Not Correct']);
            }
        }
        return response()->json([
            'status' => $result
        ]);
    }

    /**
     * Enable  Disable Gmail Accounts
     * @param Account $account
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function status(Account $account, Request $request)
    {
        $request->validate([
            'status' => ['required', 'boolean']
        ]);

        $account->is_active = $request->status;
        $result = $account->save();

        return response()->json([
            'status' => $result
        ]);
    }

    public function delete(Account $account)
    {
        $result = $account->delete();
        return response()->json([
            'status' => $result
        ]);
    }
}
