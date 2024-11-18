<?php

		namespace App\Http\Controllers;

		use Illuminate\Support\Facades\Auth;
		use Tymon\JWTAuth\Facades\JWTAuth;
		use App\Http\Controllers\Controller;
		use Illuminate\Http\Request;
		use Illuminate\Support\Facades\Hash;
		use App\Models\User;
		use App\Mail\WelcomeEmail; 
		use Illuminate\Support\Facades\Mail;

		class AuthController extends Controller
		{
   
    			public function __construct()
    			{}

   			    public function register(Request $req)
    			{

        				$this->validate($req, [
            			'name' => 'required',
                        'email' => 'required',
            			'password' => 'required|confirmed'
        				]);

        				$name = $req->input('name');
                        $email = $req->input('email');
        				$password  = Hash::make($req->input('password'));

       			 		$user = User::create(['name'=>$name, 'email'=>$email, 'password'=>$password]);

						if($user)
						{
							$details = [ 
								'title' => 'Welcome to User Subscription Management System', 
								'body' => $user->name.', your account is successfully created in User Subscription System.' 
							];

							Mail::to($user->email)->send(new WelcomeEmail($details));
							return response()->json([
								'status' => 'success', 
								'message' => 'User created successfully',
								'data' => [
									'email' => $user->email
								]
							], 200);						
						}else
						{
							return response()->json([
								'status' => 'error', 
								'message' => 'Could not create user',
								'data' => null
							],500);
						}

    			}
				
				public function login(Request $req)
    			{
						$email = $req->input('email');
        				$credentials = $req->only(['email', 'password']);
						try {
							// Attempt to verify the credentials and create an access token
							if (!$accessToken = JWTAuth::claims(['token_type' => 'access','email' => $email ])->attempt($credentials)) {
								return response()->json([
									'status' => 'error', 
									'message' => [
										'code' => '401',
										'msg' => 'Unauthorized'
									],
									'data' => null
								], 401);
							}
				
							// Generate a refresh token (could be longer-lived and distinct from the access token)
							$user = JWTAuth::user();
							$refreshToken = JWTAuth::claims(['token_type' => 'refresh'])->fromUser($user);  // Using a separate token for refreshing
				
						} catch (JWTException $e) {
							return response()->json([
								'status' => 'error', 
								'message' => [
									'code' => '500',
									'msg' => 'could_not_create_token'
								],
								'data' => null
							], 500);
						}
				
						// Return both tokens
						return response()->json([
							'status' => 'success', 
							'message' => "Token created successfully",
							'data' => [
								'access_token' => $accessToken,
								'refresh_token' => $refreshToken,
								'token_type' => 'bearer',
								'expires_in' => auth('api')->factory()->getTTL() * 60  // Time in seconds when token expires
							]
						], 201);
    			}

				public function me()
    			{
					return response()->json([
						"status" => "success",
						"message" => "User retrieved successfully.",
						"data" => [auth()->user()]
					]);
    			}

				public function logout()
    			{
        				auth()->logout();

        				return response()->json([
							"status" => "success",
							"message" => "User logged out successfully.",
							"data" => null
						]);
    			}

				public function refresh()
    			{		
						try {

							$token = JWTAuth::getPayload(JWTAuth::getToken());
							
							if ($token->get('token_type') !== 'refresh') {
								return response()->json([
									'status' => 'error', 
									'message' => [
										'code' => '403',
										'msg' => 'Only refresh tokens can be used here'
									],
									'data' => null
								], 403);
							}
					
							// Generate a new access token from the refresh token
							$newAccessToken = JWTAuth::claims(['token_type' => 'access'])->fromUser(auth()->user());
					
							return response()->json([
								'new_access_token' => $newAccessToken,
								'token_type' => 'bearer',
								'expires_in' => auth()->factory()->getTTL() * 60
							]);
					
						} catch (JWTException $e) {
							return response()->json(['error' => 'Could not refresh token'], 500);
							return response()->json([
								'status' => 'error', 
								'message' => [
									'code' => '500',
									'msg' => 'Could not refresh token'
								],
								'data' => null
							], 500);
						}
    			}

    			protected function respondWithToken($token)
    			{
        				return response()->json([
            				'access_token' => $token,
            				'token_type' => 'bearer',
            				'expires_in' => auth()->factory()->getTTL() * 60
        				]);
    			}
		}
