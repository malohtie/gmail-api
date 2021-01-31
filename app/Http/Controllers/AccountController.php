<?php

namespace App\Http\Controllers;

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

    }

    /**
     * Save Auth Account
     * @param Request $request
     */
    public function callbackAuth(Request $request)
    {

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
}
