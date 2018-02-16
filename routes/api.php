<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->group(function(){
});
Route::get('cancel-subscription', 'ProfileController@cancelSubscription');

Route::post('/calorie-goal', 'StepTwoController@storeCalorieGoal');

Route::get('meals', 'StepThreeController@getMeals');
Route::post('regenerate-meals', 'StepThreeController@regenerateMeals');
Route::post('starting-date', 'StepThreeController@saveStartingDate');

Route::get('validate-coupon', 'StepFiveController@ValidateCoupon');

Route::get('grocery-list', 'GroceryListController@getGroceryList');
Route::get('week-plans/{weekPlanId}/days/{dayIndex}', 'ProfileController@getMealsByDayIndex');

Route::post('/upload-csv', 'HomeController@uploadCsv');
