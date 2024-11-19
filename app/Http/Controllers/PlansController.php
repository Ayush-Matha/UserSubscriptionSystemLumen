<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PlansController extends Controller
{
    public function getPlans()
    {
        $key = 'All_Plans';

        if (Cache::has($key)) {
            $value = Cache::get($key);

            return response()->json([
                'status' => 'success', 
                'message' => 'Plan retrived successfully',
                'data' => $value
            ], 200);
        }
        else
        {
            $value = DB::select("select * from plans");

            $ttl = 60; // Time to live in minutes (default: 60)

            // Store in Memcached
            Cache::put($key, $value, $ttl);

            if (Cache::has($key)) {
                $value = Cache::get($key);
    
                return response()->json([
                    'status' => 'success', 
                    'message' => 'Plan retrived successfully',
                    'data' => $value
                ], 200);
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Key not found in cache',
            'data' => null
        ], 404);
    }

    public function getPlanById($id)
    {
        $plan = DB::select('select * from plans where plan_id = ?',[$id]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Plan Retrived Successfully',
            'data' => $plan
        ]);
    }

    public function updatePlan(Request $request)
    {
        // Retrieve the parameters from the request
        $planId = $request->input('plan_id');
        $name = $request->input('name');
        $amount = $request->input('amount');
        $description = $request->input('description');

        // Validate the inputs
        $this->validate($request,[
        'plan_id' => 'required|exists:plans,plan_id',
        'name' => 'required|string|max:255',
        'amount' => [
            'required',
            'json',
            function ($attribute, $value, $fail) {
                $decodedValue = json_decode($value, true);
                // Check if the decoding failed or the required keys are missing
                if (!is_array($decodedValue) || !isset($decodedValue['monthly'], $decodedValue['yearly'])) {
                    return $fail('The ' . $attribute . ' must be a JSON object containing both "monthly" and "yearly" properties.');
                }

                // Check if there are any additional keys
                if (count(array_diff(array_keys($decodedValue), ['monthly', 'yearly'])) > 0) {
                    return $fail('The ' . $attribute . ' must only contain the "monthly" and "yearly" properties.');
                }

                // Check if the values are numeric
                if (!is_numeric($decodedValue['monthly']) || !is_numeric($decodedValue['yearly'])) {
                    return $fail('The values for "monthly" and "yearly" in ' . $attribute . ' must be numeric.');
                }
            }
        ],
        'description' => 'required|string'
        ]);

        $updatedPlan = DB::update("UPDATE plans SET name = ?, amount = ?, description = ? WHERE plan_id = ?", [
                                $name,
                                $amount,
                                $description,
                                $planId
                            ]);
        
        if ($updatedPlan) {
            return response()->json([
                'status' => 'success', 
                'message' => 'Plan updated successfully',
                'data' => null
            ], 200);
        } else {
            return response()->json([
                'status' => 'error', 
                'message' => 'No changes made or plan not found',
                'data' => null
            ], 404);
        }
    }

    public function insertPlan(Request $request)
    {
        // Validate the request data
        $this->validate($request,[
            'name' => 'required|string|max:255',
            'amount' => [
                'required',
                'json',
                function ($attribute, $value, $fail) {
                    $decodedValue = json_decode($value, true);
                    // Check if the decoding failed or the required keys are missing
                    if (!is_array($decodedValue) || !isset($decodedValue['monthly'], $decodedValue['yearly'])) {
                        return $fail('The ' . $attribute . ' must be a JSON object containing both "monthly" and "yearly" properties.');
                    }

                    // Check if there are any additional keys
                    if (count(array_diff(array_keys($decodedValue), ['monthly', 'yearly'])) > 0) {
                        return $fail('The ' . $attribute . ' must only contain the "monthly" and "yearly" properties.');
                    }

                    // Check if the values are numeric
                    if (!is_numeric($decodedValue['monthly']) || !is_numeric($decodedValue['yearly'])) {
                        return $fail('The values for "monthly" and "yearly" in ' . $attribute . ' must be numeric.');
                    }
                }
            ],
            'description' => 'required|string',
        ]);

        // Retrieve the validated data from the request
        $name = $request->input('name');
        $amount = $request->input('amount');
        $description = $request->input('description');

        // Insert the new plan into the database
        $inserted = DB::insert("INSERT INTO plans (name, amount, description) VALUES (?, ?, ?)", [
            $name,
            $amount,
            $description
        ]);

        // Check if the insertion was successful
        if ($inserted) {
            return response()->json([
                'status' => 'success', 
                'message' => 'Plan inserted successfully',
                'data' => null
            ], 201);
        } else {
            return response()->json([
                'status' => 'error', 
                'message' => 'Failed to insert plan',
                'data' => null
            ], 500);
        }
    }
}
