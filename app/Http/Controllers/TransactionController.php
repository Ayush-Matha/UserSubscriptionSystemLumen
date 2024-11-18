<?php

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use App\Models\Transaction;
// use App\Models\Subscription;
// use Illuminate\Support\Facades\DB;
// use Carbon\Carbon;

// class TransactionController extends Controller
// {
//     public function createTransaction(Request $request)
//     {
//         // return response()->json($request);
//         $this->validate($request,[
//             'user_id' => 'required|exists:users,id',
//             'amount' => 'required',
//             'payment_type' => 'required|in:UPI,Credit Card,Debit Card',
//             'plan_id' => 'required|exists:plans,plan_id',
//             'payment_option_details' => 'required|json'
//         ]);

//         $user_id = $request->input('user_id');
//         $amount = $request->input('amount');
//         $payment_type = $request->input('payment_type');
//         $plan_id = $request->input('plan_id');
//         $payment_option_details = $request->input('payment_option_details');
        
//         $transaction = Transaction::create([
//             'user_id' => $user_id, 
//             'amount' => $amount, 
//             'payment_type' => $payment_type, 
//             'plan_id' => $plan_id, 
//             'payment_option_details' => $payment_option_details
//         ]);

//         if($transaction)
//         {
//             $plan_validity = DB::select("select validity from plans where plan_id = ?",[$transaction->plan_id]);

//             // Assuming `validity` is in months, extract the value
//             $validity = $plan_validity[0]->validity ?? 0; // Default to 0 if not found

//             // Calculate the expiry date by adding `$validity` months to the current date
//             $expiryDate = Carbon::now()->addMonths($validity)->toDateString(); // Format as YYYY-MM-DD

//             $subscription = Subscription::create([
//                 't_id' => $transaction->transaction_id,
//                 'u_id' => $transaction->user_id,
//                 'plan_id' => $transaction->plan_id,
//                 'expiry' => $expiryDate
//             ]);

//             return response()->json("Transaction Created Successfully");
//         }

        

//     }
// }
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function createTransaction(Request $request)
    {
        // return response()->json($request);
        $this->validate($request,[
            'user_id' => 'required|exists:users,id',
            'amount' => 'required',
            'payment_type' => 'required|in:UPI,Credit Card,Debit Card',
            'plan_id' => 'required|exists:plans,plan_id',
            'payment_option_details' => 'required|json'
        ]);

        $user_id = $request->input('user_id');
        $amount = $request->input('amount');
        $payment_type = $request->input('payment_type');
        $plan_id = $request->input('plan_id');
        $payment_option_details = $request->input('payment_option_details');
        
        $transaction = Transaction::create([
            'user_id' => $user_id, 
            'amount' => $amount, 
            'payment_type' => $payment_type, 
            'plan_id' => $plan_id, 
            'payment_option_details' => $payment_option_details
        ]);

        if($transaction)
        {
            $plan_validity = DB::select("select validity from plans where plan_id = ?",[$transaction->plan_id]);

            // Assuming `validity` is in months, extract the value
            $validity = $plan_validity[0]->validity ?? 0; // Default to 0 if not found

            // Calculate the expiry date by adding `$validity` months to the current date
            $expiryDate = Carbon::now()->addMonths($validity)->toDateString(); // Format as YYYY-MM-DD

            $subscription = Subscription::create([
                't_id' => $transaction->transaction_id,
                'u_id' => $transaction->user_id,
                'plan_id' => $transaction->plan_id,
                'expiry' => $expiryDate
            ]);

            return response()->json([
                'status' => 'success', 
                'message' => 'User subscribed successfully',
                'data' => null
            ], 200);
        }

        

    }
}
