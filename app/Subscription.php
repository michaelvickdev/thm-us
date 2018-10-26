<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'user_id', 'name', 'braintree_id', 'braintree_plan', 'stripe_id', 'stripe_plan', 'quantity', 'trial_ends_at', 'ends_at'
    ];

    public function main() {
        return $this->ends_at;
    }
}
