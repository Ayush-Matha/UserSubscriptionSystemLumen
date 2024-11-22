<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->options('/{any:.*}', function () {
    return response('', 200);
});


$router->get('/', function () use ($router) {
    return $router->app->version();
});

//Authentication Routes
$router->post('/login', 'AuthController@login');
$router->post('/register', 'AuthController@register');
$router->post('/refresh','AuthController@refresh');
$router->group(['middleware' => ['auth:api', 'verify_access']], function () use ($router) {
    $router->post('/me', 'AuthController@me');
    $router->post('/logout', 'AuthController@logout');
});

//Plans Routes
$router->post('/insert-plan', 'PlansController@insertPlan');
$router->get('/all-plans', 'PlansController@getPlans');
$router->get('/get-plan-by-id/{id}', 'PlansController@getPlanById');
$router->post('/update-plan', 'PlansController@updatePlan');

//Transaction Routes
$router->post('/create-transaction','TransactionController@createTransaction');

