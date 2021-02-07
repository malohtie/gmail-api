<?php

namespace App\Http\Controllers;

use App\Models\Api;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function index(Request $request)
    {
        if($request->ajax()) {
            return response()->json(['data' => Api::withCount('accounts')->get()]);
        }
        return view('apis');
    }

    public function add(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
        ]);

        $res = Api::create([
            'name' => $request->name,
            'client_id' => $request->client_id,
            'client_secret' => $request->client_secret,
            'is_active' => true
        ]);

        return response()->json([
            'status' => $res,
        ]);
    }

    public function status(Api $api, Request $request)
    {
        $request->validate([
            'status' => ['required', 'boolean']
        ]);

        $api->is_active = $request->status;
        $result = $api->save();

        return response()->json([
            'status' => $result
        ]);
    }

    /*public function delete(Api $api)
    {
        $result = $api->delete();
        return response()->json([
            'status' => $result
        ]);
    }*/
}
