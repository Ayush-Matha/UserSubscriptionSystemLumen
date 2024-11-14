<?php

		namespace App\Http\Controllers;

		use Illuminate\Support\Facades\Auth;
		use Tymon\JWTAuth\Facades\JWTAuth;
		use App\Http\Controllers\Controller;
		use Illuminate\Http\Request;
		use Illuminate\Support\Facades\Hash;
		use App\Models\User;

		class AuthController extends Controller
		{
   
    			public function __construct()
    			{
       				//  $this->middleware('auth:api', ['except' => ['login','register']]);
    			}

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

       			 	User::create(['name'=>$name, 'email'=>$email, 'password'=>$password]);

        				return response()->json(['status'=>'success', 'operation'=>'created']);
    			}
				
 
   			    public function login(Request $req)
    			{
        				$credentials = $req->only(['email', 'password']);
						try {
							// Attempt to verify the credentials and create an access token
							if (!$accessToken = JWTAuth::claims(['token_type' => 'access'])->attempt($credentials)) {
								return response()->json(['error' => 'invalid_credentials'], 401);
							}
				
							// Generate a refresh token (could be longer-lived and distinct from the access token)
							$user = JWTAuth::user();
							$refreshToken = JWTAuth::claims(['token_type' => 'refresh'])->fromUser($user);  // Using a separate token for refreshing
				
						} catch (JWTException $e) {
							return response()->json(['error' => 'could_not_create_token'], 500);
						}
				
						// Return both tokens
						return response()->json([
							'access_token' => $accessToken,
							'refresh_token' => $refreshToken,
							'token_type' => 'bearer',
							'expires_in' => auth('api')->factory()->getTTL() * 60  // Optional: set token expiration in seconds
						]);
    			}

    			public function me()
    			{
        				return response()->json(auth()->user());
    			}

    			public function logout()
    			{
        				auth()->logout();

        				return response()->json(['message' => 'Successfully logged out']);
    			}

			    public function refresh()
    			{
        				// return $this->respondWithToken(auth()->refresh());
						// return response()->json([
						// 	'new_access_token' => auth()->refresh(),
						// 	'token_type' => 'bearer',
						// 	'expires_in' => auth()->factory()->getTTL() * 60
						// ]);
						
						try {
							// Validate that the incoming token is a refresh token
							// $user = JWTAuth::parseToken()->authenticate();

							$token = JWTAuth::getPayload(JWTAuth::getToken());
							
							if ($token->get('token_type') !== 'refresh') {
								return response()->json(['error' => 'Only refresh tokens can be used here'], 403);
							}
					
							// Generate a new access token from the refresh token
							$newAccessToken = JWTAuth::claims(['token_type' => 'access'])->fromUser(auth()->user());

							// $newRefreshToken = JWTAuth::claims(['token_type' => 'refresh'])->fromUser($user);
					
							return response()->json([
								'new_access_token' => $newAccessToken,
								// 'new_refresh_token' => $newRefreshToken,
								'token_type' => 'bearer',
								'expires_in' => auth()->factory()->getTTL() * 60
							]);
					
						} catch (JWTException $e) {
							return response()->json(['error' => 'Could not refresh token'], 500);
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
