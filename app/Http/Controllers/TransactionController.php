<?php
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
            'payment_type' => 'required|in:UPI,Credit Card,Bank Transfer',
            'plan_id' => 'required|exists:plans,plan_id',
            'payment_option_details' => [
                'required',
                'json',
                function ($attribute, $value, $fail) {
                    // Decode the JSON string
                    $decoded = json_decode($value, true);
        
                    // Check if decoding failed or the required key is missing
                    if (!is_array($decoded) || !isset($decoded['validity'])) {
                        return $fail('The ' . $attribute . ' must be a valid JSON object containing a "validity" property.');
                    }
        
                    // Optional: Validate the "validity" property (if it should be specific values)
                    if (!in_array($decoded['validity'], ['monthly', 'yearly'])) {
                        return $fail('The "validity" property in ' . $attribute . ' must be either "monthly" or "yearly".');
                    }
                }
            ]
        ]);

        $user_id = $request->input('user_id');
        $payment_type = $request->input('payment_type');
        $plan_id = $request->input('plan_id');
        $payment_option_details = $request->input('payment_option_details');
        
        $transaction = Transaction::create([
            'user_id' => $user_id,
            'payment_type' => $payment_type, 
            'plan_id' => $plan_id, 
            'payment_option_details' => $payment_option_details
        ]);

        if($transaction)
        {
            $plan_details = DB::select("select * from plans where plan_id = ?",[$transaction->plan_id]);

            // Decode the JSON string to an associative array
            $paymentOptionDetails = json_decode($transaction->payment_option_details, true);

            if(is_array($paymentOptionDetails))
            {
                $validity = $paymentOptionDetails['validity']; // it will contain 'monthly' or 'yearly'

                if($validity === 'monthly')
                {
                    // Calculate the expiry date by adding `$validity` months to the current date
                    $expiryDate = Carbon::now()->addMonths(1)->toDateString(); // Format as YYYY-MM-DD   
                }
                else
                {
                    // Calculate the expiry date by adding `$validity` months to the current date
                    $expiryDate = Carbon::now()->addMonths(12)->toDateString(); // Format as YYYY-MM-DD
                }
            }


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
