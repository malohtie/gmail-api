<?php

namespace App\Http\Controllers;

use App\Libs\Gmail;
use App\Models\Account;
use Illuminate\Http\Request;

class FromController extends Controller
{
    public function index()
    {
        return view('froms', [
            'accounts' => Account::where('is_active', true)->whereNotNull('token')->get(['id', 'email'])
        ]);
    }

    public function make(Account $account, Request $request)
    {
        $request->validate([
            'from' => ['required', 'string'],
        ]);

        $gmail = new Gmail($account);
        $result = $gmail->setFrom($request->from);
        return response()->json($result);
    }
}
