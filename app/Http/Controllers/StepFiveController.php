<?php

namespace App\Http\Controllers;

use App\Models\DiscountCode;
use App\Plan;
use Braintree_ClientToken;
use Illuminate\Http\Request;

class StepFiveController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function index(Request $request)
    {
        $discount = false;
        //$plans = Plan::orderBy('cost')->get();

        $plans = Plan::where('is_discount', false)
            //->where('show_on_homepage', true)
            ->orderBy('month')
            ->get();

        $selectedPlan = $request->query->get('plan') ?? $plans->last()->id;

        //$braintreeToken = Braintree_ClientToken::generate();

        return view('steps.step5')->with([
            'thehotmealPlans' => $plans,
            'selectedPlan' => $selectedPlan,
            'discount' => $discount,
        ]);
    }

    public function validateCoupon(Request $request){

        $code = $request->input('coupon-code');

        $discount = DiscountCode::validateCode($code);

        if(is_null($discount) || $discount->is_activated){
            return response()->json(false);
        }
        $plan = $discount->plan()->first();
        if(is_null($plan)){
            return response()->json(false);
        }
        $planName = $plan->name;
        $price = $plan->cost;
        $planId = $plan->id;
        return response()->json(array('discount' => $discount,'planId' => $planId, 'planName' => $planName, 'price' => $price));
    }
}
