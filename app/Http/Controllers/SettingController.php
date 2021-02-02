<?php

namespace App\Http\Controllers;

use App\Libs\Gmail;
use App\Models\Account;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return view('settings', [
            'accounts' => Account::where('is_active', true)->whereNotNull('token')->get(['id', 'email'])
        ]);
    }

    public function make(Account $account, Request $request)
    {
        $request->validate([
            'from' => ['nullable', 'string'],
            'subject' => ['required', 'string'],
            'body' => ['required', 'string'],
        ]);

        if($account->is_active && !empty($account->token)) {
            $gmail = new Gmail($account);
            $vacation = $gmail->setVacation($request->subject, $request->body);
            return response()->json([
                'status' => true,
                'message' => 'OK'
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => 'Account Not Active Or Auth Not Set'
        ]);
    }
}
