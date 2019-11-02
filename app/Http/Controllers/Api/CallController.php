<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\User;
use App\Call;
use OpenTok\OpenTok;

class CallController extends Controller
{

    /**
     * getAvailableEmployee
     *
     * @return \App\User
     */
    private function getAvailableEmployee()
    {
        $selectedEmployee = User::whereHas('roles', function ($query) {
            $query->where('name', 'employee');
        })->where('status', 'online')->first();

        return $selectedEmployee;
    }

    /**
     * notifyEmployee
     *
     * Notify an employee of an incoming call
     *
     * @param \App\User $employee
     * @param \App\Call $call
     * @return void
     */
    private function notifyEmployee(User $employee, Call $call)
    {

    }

    /**
     * requestCall
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function requestCall(Request $request)
    {
        $customer = Auth::user();

        $employee = $this->getAvailableEmployee();

        if($employee === null){
            $message = [
                'type' => 'error',
                'message' => 'Currently there are not employees available to take the call',
            ];
            return response($message, 200);
        }

        $call = new Call;
        $call->caller_id = $customer->id;
        $call->recipient_id = $employee->id;

        $call->save();

        $response = [
            'call' => [
                'id' => $call->id,
                'customer' => $customer->name,
                'employee' => $employee->name,
            ]
        ];

        return response($response, 200);
    }


}
