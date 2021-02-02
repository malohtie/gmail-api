<?php

namespace App\Http\Controllers;

use App\Libs\Gmail;
use App\Models\Account;
use Illuminate\Http\Request;

class VacationController extends Controller
{
    public function index()
    {
        return view('vacations', [
            'accounts' => Account::where('is_active', true)->whereNotNull('token')->get(['id', 'email'])
        ]);
    }

    public function make(Account $account, Request $request)
    {
        $request->validate([
            'subject' => ['required', 'string'],
            'body' => ['required', 'string'],
        ]);

        if($account->is_active && !empty($account->token)) {
            $gmail = new Gmail($account);
            $vacation = $gmail->setVacation($request->subject, $request->body);
            return response()->json([
                'status' => $vacation,
                'message' => $vacation ? 'OK' : 'ERROR'
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Account Not Active Or Auth Not Set'
        ]);
    }
}
