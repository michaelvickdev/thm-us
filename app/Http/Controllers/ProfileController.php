<?php

namespace App\Http\Controllers;

use App\DailyAdditional;
use App\Models\DayMenu;
use App\Models\Meal;
use App\Models\WeekPlan;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function myProfile()
    {
        $user = Auth::user();
        $format = 'd F Y';
        $date = date($format, time());
        $weekPlan = $user->latestWeekPlan();

        return view('profile.my_profile', array(
            'user' => $user,
            'date' => $date,
            'startDate' => $weekPlan->start_date,
            'weekPlanId' => $weekPlan->id));
    }

    public function dayView($weekPlanId, $dayIndex)
    {
        $user = Auth::user();
        $format = 'd F Y';
        $date = date($format, time());
        $weekPlan = WeekPlan::find($weekPlanId);

        if(is_null($weekPlan) || $dayIndex > 7){
            return response('Bad request', 400);
        }
        if($weekPlan->user_id !== $user->id){
            return response('Unauthorized', 401);
        }
        return view('profile.day_view', array(
            'user' => $user,
            'date' => $date));
    }

    public function mealCompleted($mealId,$weekPlanId, $day) {
        $meal = DayMenu::with('meal')->where('meal_id', $mealId)->where('week_plan_id', $weekPlanId)->where('day', $day)->first();
        $completed = !$meal->meal_completed;
        $meal->update(['meal_completed' => $completed]);
        $f = DailyAdditional::where('week_plan_id', $weekPlanId)->where('day', $day)->first();
        //dd($f);
        if($completed) {
            $f->completed_sum += $meal->meal->calories;
            $f->save();
        } else {
            $f->completed_sum -= $meal->meal->calories;
            $f->save();
        }

        return response($meal);
    }

    public function getMealsByDayIndex($weekPlanId){
        $user = Auth::user();
        $weekPlan = WeekPlan::find($weekPlanId);

        if(is_null($weekPlan)){
            return response('Bad request', 400);
        }
        if($weekPlan->user_id !== $user->id){
            return response('Unauthorized', 401);
        }
        foreach($weekPlan->dayMenus->groupBy('day') as $dayMenu) {
            $day = $dayMenu[0]['day'];
            $responseMenu[$day][Meal::BREAKFAST]['name'] = 'Breakfast';
            $responseMenu[$day][Meal::BREAKFAST]['meals'] = Meal::getMealByIds($dayMenu->where('time_of_day', 'Breakfast')->pluck('meal_id'), $weekPlan->id, $day);
            $responseMenu[$day][Meal::BREAKFAST]['calories'] = $responseMenu[$day][Meal::BREAKFAST]['meals']->sum('calories');
            $responseMenu[$day][Meal::LUNCH]['name'] = 'Lunch';
            $responseMenu[$day][Meal::LUNCH]['meals'] = Meal::getMealByIds($dayMenu->where('time_of_day', 'Lunch')->pluck('meal_id'), $weekPlan->id, $day);
            $responseMenu[$day][Meal::LUNCH]['calories'] = $responseMenu[$day][Meal::LUNCH]['meals']->sum('calories');
            $responseMenu[$day][Meal::DINNER]['name'] = 'Dinner';
            $responseMenu[$day][Meal::DINNER]['meals'] = Meal::getMealByIds($dayMenu->where('time_of_day', 'Dinner')->pluck('meal_id'), $weekPlan->id, $day);
            $responseMenu[$day][Meal::DINNER]['calories'] = $responseMenu[$day][Meal::DINNER]['meals']->sum('calories');
            $allMealsForDay = DayMenu::where('week_plan_id', $weekPlan->id)->where('day', $day)->where('meal_completed', true)->select('meal_id')->pluck('meal_id')->toArray();

            $responseMenu[$day]['totalcalories'] = Meal::whereIn('id', $allMealsForDay)->sum('calories');
        }
        return response()->json($responseMenu);
    }

    public function accountSettings()
    {
        $user = Auth::user();
        $isGrace = false;
        if ($user->subscription('main')->onGracePeriod()) {
            $isGrace = true;
        }
        $subscription = $user->subscription('main')->asBraintreeSubscription();
        return view('profile.account_settings',array(
            'subscription' => $subscription,
            'user' => $user,
            'isGrace' => $isGrace));
    }

    public function updateUserData() {
        $request = request();
        $validator = \Validator::make($request->all(), [
            'first_name' => 'required|string|min:2',
            'last_name' => 'required|string|min:2',
            'old_password' => 'nullable|string|min:5',
            'password' => 'nullable|required_with:old_password|string|min:5',
        ]);
        if($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        if($request->has('old_password')) {
            if (!Hash::check($request->old_password, Auth::user()->password)) {
                $validator->errors()->add('old_password', 'You did not type in the correct old password');
                return redirect()->back();
            }
        }

        session()->flash('message', 'You have updated your details.');
        return redirect()->back();
    }

    public function subscriptionInfo()
    {
        $user = Auth::user();
        $isGrace = false;
        if ($user->subscription('main')->onGracePeriod()) {
            $isGrace = true;
        }
            $subscription = $user->subscription('main')->asBraintreeSubscription();
//         = ($subscription->nextBillingDate->format('d F Y'));
        return view('profile.subscription_info',array(
            'subscription' => $subscription,
            'user' => $user,
            'isGrace' => $isGrace));
    }


    public function markMealAsFavorite($id) {
        Auth::user()->favoriteMeals()->toggle($id);
        return response('OK!', 200);
    }

    public function cancelSubscription(){
        $user = Auth::user();
        if (!$user->subscription('main')->onGracePeriod()) {
            $user->subscription('main')->cancel();
        }
        return redirect()->back();
    }

    public function resumeSubscription() {
        $user = Auth::user();
        if ($user->subscription('main')->onGracePeriod()) {
            $user->subscription('main')->resume();
        }
        return redirect()->back();
    }

    public function groceryList()
    {
        return view('profile.grocery_list');
    }
}
