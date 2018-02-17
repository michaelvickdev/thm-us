<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    const BREAKFAST = 0;
    const LUNCH = 1;
    const DINNER = 2;
    const SNACKS = 3;

    public static $types = [
        self::BREAKFAST => 'Breakfast',
        self::LUNCH => 'Lunch',
        self::DINNER => 'Dinner',
        self::SNACKS => 'Snacks',
    ];

    public static $indexes = [
        'Breakfast' => self::BREAKFAST,
        'Lunch' => self::LUNCH,
        'Dinner' => self::DINNER,
        'Snacks' => self::SNACKS
        ];

    protected $fillable = [
        'name', 'serving_size', 'calories', 'image', 'notes', 'is_snack', 'store', 'is_enabled'
    ];
    public $timestamps = false;

    protected $appends = ['favorite'];
    
    public function condiment(){
        return $this->hasOne('App\Models\Condiment');
    }

    public function getFavoriteAttribute() {
        $user = \Auth::user();
        if(!$user) {
            return false;
        }
        if($user->favoriteMeals->contains($this->id)) {
            return true;
        }
        return false;
    }

    public function getImageAttribute($v) {
        return '/img/food/'.$v;
    }

    public static function createMeal($record)
    {
        $meal = self::firstOrNew(array(
            'name' => $record['MEAL'],
            'serving_size' => $record['SERVING SIZE'],
            'calories' => $record['CALORIES PER SERVING'],
            'image' => '/img/food/' . $record['IMAGE'],
            'notes' => $record['NOTES'],
            'is_snack' => $record['SNACK OR NOT'],
            'store' => $record['STORE']
        ));
        $meal->save();
        return $meal;
    }

    public static function generateMealsForOneDay($calorieGoal, $ignoredMealIds)
    {
        $breakfastMaxCalories = $calorieGoal * 0.2;
        $LunchMaxCalories = $calorieGoal * 0.3;
        $DinnerMaxCalories = $calorieGoal * 0.3;
        $snacksMaxCalories = $calorieGoal * 0.2;

        list($breakfastMeals, $breakfastCalories, $ignoredMealIds) = self::getMealsForTimeOfDay($breakfastMaxCalories, $ignoredMealIds, false);
        list($lunchMeals, $lunchCalories,$ignoredMealIds) = self::getMealsForTimeOfDay($LunchMaxCalories, $ignoredMealIds, false);
        list($dinnerMeals, $dinnerCalories, $ignoredMealIds) = self::getMealsForTimeOfDay($DinnerMaxCalories, $ignoredMealIds, false);
        list($snacksMeals, $snacksCalories, $ignoredMealIds) = self::getMealsForTimeOfDay($snacksMaxCalories, $ignoredMealIds, true);

        if (!empty($breakfastMeals)) {
            $dayMenu[0]['name'] = 'Breakfast';
            $dayMenu[0]['calories'] = $breakfastCalories;
            $dayMenu[0]['meals'] = $breakfastMeals;
        }
        if (!empty($lunchMeals)) {
            $dayMenu[1]['name'] = 'Lunch';
            $dayMenu[1]['calories'] = $lunchCalories;
            $dayMenu[1]['meals'] = $lunchMeals;
        }
        if (!empty($dinnerMeals)) {
            $dayMenu[2]['name'] = 'Dinner';
            $dayMenu[2]['calories'] = $dinnerCalories;
            $dayMenu[2]['meals'] = $dinnerMeals;
        }
        if (!empty($snacksMeals)) {
            $dayMenu[3]['name'] = 'Snacks';
            $dayMenu[3]['calories'] = $snacksCalories;
            $dayMenu[3]['meals'] = $snacksMeals;
        }
        return array($dayMenu, $ignoredMealIds);
    }

    public static function getMealsForTimeOfDay($maxCalories, $ignoredMealIds, $isSnack)
    {
        $meals = [];
        $mealCalories = 0;
        while ($mealCalories < $maxCalories) {

                $meal = Meal::where('is_enabled', true)->where('calories', '<=', ($maxCalories - $mealCalories))
                    ->where('is_snack', $isSnack)
                    ->whereNotIn('id', $ignoredMealIds)
                    ->with('condiment')
                    ->inRandomOrder()->first();

            if (is_null($meal)) {
                break;
            }
            if ($meal->calories + $mealCalories < $maxCalories) {
                array_push($meals, $meal->toArray());
                $ignoredMealIds[] = $meal->id;
                $mealCalories += $meal->calories;
            } else {
                break;
            }
        }
        return array($meals, $mealCalories, $ignoredMealIds);
    }

    public static function getMealByIds($mealIds, $weekPlanId, $day)
    {
        //return self::whereIn('meals.id', $mealIds)->get();

        return self::select(\DB::raw('distinct meals.*, day_menus.meal_completed'))
            ->whereIn('meals.id', $mealIds)
            ->leftJoin('day_menus', 'meals.id', '=', 'day_menus.meal_id')
            ->where('week_plan_id', $weekPlanId)
            ->where('day', $day)
            ->get();
    }
}
