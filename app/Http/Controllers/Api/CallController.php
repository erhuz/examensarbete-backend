<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\User;
use App\Call;
use App\Events\CallAccepted;
use App\Events\CallRequested;
use App\Events\UserStatusUpdated;
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
     * initializeCall
     *
     * @param \App\Call $call
     * @return void
     */
    private function initializeCall(Call $call)
    {
        # Initialize call
        // Create OT-session

        // Create employee token

        // Create customer token

        // Update the call

        // Dispatch CallInitialized event to employee

        // Dispatch CallInitialized event to customer

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

        // Dispatch CallRequested with call as paramater
        CallRequested::dispatch($call);

        $employee->status = 'busy';
        $employee->save();

        // Dispatch UserStatusUpdated event
        UserStatusUpdated::dispatch($employee);


        return response($call, 200);
    }


    public function acceptCall(Request $request, Call $call)
    {
        $employee = Auth::user();
        if($employee->id !== $call->recipient_id){
            $message = [
                'type' => 'Error',
                'message' => 'You are not allowed to access this endpoint'
            ];
            return response($message, 403);
        }

        // Make some modifications & method calls here
        CallAccepted::dispatch($call);
        $this->initializeCall($call);

        return response($call, 200);
    }
}
