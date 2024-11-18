<?php
// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;

// class PlansController extends Controller
// {
//     public function getPlans()
//     {
//         $allPlans = DB::select("select * from plans");
//         return response()->json(['status' => 'success', 'message' => '', 'data' => $allPlans]);
//     }

//     public function updatePlan(Request $request)
//     {
//         // Retrieve the parameters from the request
//         $planId = $request->input('plan_id');
//         $name = $request->input('name');
//         $amount = $request->input('amount');
//         $validity = $request->input('validity');
//         $description = $request->input('description');

//         // Validate the inputs (optional but recommended)
//         $this->validate($request,[
//         'plan_id' => 'required|exists:plans,plan_id',
//         'name' => 'required|string|max:255',
//         'amount' => 'required|numeric',
//         'validity' => 'required|integer',
//         'description' => 'required|string'
//         ]);

//         $updatedPlan = DB::update("UPDATE plans SET name = ?, amount = ?, validity = ?, description = ? WHERE plan_id = ?", [
//                                 $name,
//                                 $amount,
//                                 $validity,
//                                 $description,
//                                 $planId
//                             ]);
        
//         if ($updatedPlan) {
//             return response()->json(['message' => 'Plan updated successfully'], 200);
//         } else {
//             return response()->json(['message' => 'No changes made or plan not found'], 404);
//         }
//     }

//     public function insertPlan(Request $request)
//     {
//         // Validate the request data
//         $this->validate($request,[
//             'name' => 'required|string|max:255',
//             'amount' => 'required|numeric',
//             'validity' => 'required|integer',
//             'description' => 'required|string',
//         ]);

//         // Retrieve the validated data from the request
//         $name = $request->input('name');
//         $amount = $request->input('amount');
//         $validity = $request->input('validity');
//         $description = $request->input('description');

//         // Insert the new plan into the database
//         $inserted = DB::insert("INSERT INTO plans (name, amount, validity, description) VALUES (?, ?, ?, ?)", [
//             $name,
//             $amount,
//             $validity,
//             $description
//         ]);

//         // Check if the insertion was successful
//         if ($inserted) {
//             return response()->json(['message' => 'Plan inserted successfully'], 201);
//         } else {
//             return response()->json(['message' => 'Failed to insert plan'], 500);
//         }
//     }
// }
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlansController extends Controller
{
    public function getPlans()
    {
        $allPlans = DB::select("select * from plans");
        return response()->json([
            'status' => 'success', 
            'message' => 'Plan retrived successfully',
            'data' => $allPlans
        ], 200);
    }

    public function updatePlan(Request $request)
    {
        // Retrieve the parameters from the request
        $planId = $request->input('plan_id');
        $name = $request->input('name');
        $amount = $request->input('amount');
        $validity = $request->input('validity');
        $description = $request->input('description');

        // Validate the inputs
        $this->validate($request,[
        'plan_id' => 'required|exists:plans,plan_id',
        'name' => 'required|string|max:255',
        'amount' => 'required|numeric',
        'validity' => 'required|integer',
        'description' => 'required|string'
        ]);

        $updatedPlan = DB::update("UPDATE plans SET name = ?, amount = ?, validity = ?, description = ? WHERE plan_id = ?", [
                                $name,
                                $amount,
                                $validity,
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
            'amount' => 'required|numeric',
            'validity' => 'required|integer',
            'description' => 'required|string',
        ]);

        // Retrieve the validated data from the request
        $name = $request->input('name');
        $amount = $request->input('amount');
        $validity = $request->input('validity');
        $description = $request->input('description');

        // Insert the new plan into the database
        $inserted = DB::insert("INSERT INTO plans (name, amount, validity, description) VALUES (?, ?, ?, ?)", [
            $name,
            $amount,
            $validity,
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
