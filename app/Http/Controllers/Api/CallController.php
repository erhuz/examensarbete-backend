<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\User;
use App\Call;
use App\Events\CallAccepted;
use App\Events\CallInitialized;
use App\Events\CallRequested;
use App\Events\UserStatusUpdated;
use App\Events\EndCall;
use OpenTok\OpenTok;
use OpenTok\Role;

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
        $opentok = new OpenTok(env('OPENTOK_API_KEY'), env('OPENTOK_API_SECRET'));

        $employee = User::where('id', $call->recipient_id)->first();
        $customer = User::where('id', $call->caller_id)->first();

        $employeeId = $employee->id;
        $customerId = $customer->id;

        # Initialize call
        // Create OT-session
        $session = $opentok->createSession([
            'expireTime' => time()+(24 * 60 * 60), // in one day
        ]);

        // Create employee token
        $employeeToken = $session->generateToken([
            'role'       => Role::MODERATOR,
            'expireTime' => time()+(24 * 60 * 60), // in one day
        ]);

        // Create customer token
        $customerToken = $session->generateToken();

        // Update the call
        $call->session_id = $session->getSessionId();
        $call->caller_token = $customerToken;
        $call->recipient_token = $employeeToken;
        $call->status = 'active';
        $call->save();

        // Dispatch CallInitialized event to employee
        CallInitialized::dispatch($call, $employee);

        // Dispatch CallInitialized event to customer
        CallInitialized::dispatch($call, $customer);

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

    public function endCall(Request $request, Call $call)
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
        EndCall::dispatch($call);

        return response($call, 200);
    }
}
