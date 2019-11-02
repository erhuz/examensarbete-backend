<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function user(Request $request)
    {
        $user = $request->user();
        $tmp = $user->roles[0];

        return $user;
    }

    public function setStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => [
                'required',
                'string',
                'max:16',
                Rule::in(['online', 'busy', 'on_break', 'offline']),
            ],
        ]);

        if ($validator->fails())
        {
            return response(['errors'=>$validator->errors()->all()], 422);
        }

        $user = Auth::user();
        $response = ['status' => $request['status']];

        if($user->status === $request['status']){
            return response($response, 304);
        }

        $user->status = $request['status'];
        $user->save();

        return response($response, 200);
    }
}
