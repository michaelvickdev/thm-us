@extends('layouts.app')

@section('content')
    @include('layouts.navbar')
    <div class="container" id="container-profile ">
        <div class="col-lg-12 col-xs-12 col-sm-12" id="box-user-profile">
            <div class="col-lg-6 col-xs-12 col-sm-6">
                <h2>Account settings</h2>
            </div>

            <div class="col-lg-12">
                <div class="box-form">
                    @if(session('message'))
                        @if(is_string(session('message')))
                            <div class="alert alert-info">{{ session('message') }}</div>
                        @else
                            <div class="alert alert-{{@session('message')['type'] ?: 'success'}}">{{ @session('message')['message'] ?: session('message') }}</div>
                        @endif
                    @endif
                    <form class="form-horizontal" method="POST" action="{{ route('update-user-data') }}"
                          autocomplete="off">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <div class="col-lg-6 col-sm-6 col-md-6{{ $errors->has('first_name') ? ' has-error' : '' }}">
                                <label for="first_name">First Name</label>
                                <input id="first_name" type="text" class="form-control" name="first_name"
                                       value="{{ $user->first_name }}" required autofocus>

                                @if ($errors->has('first_name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('first_name') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-lg-6 col-sm-6 col-md-6{{ $errors->has('last_name') ? ' has-error' : '' }}">
                                <label for="last_name">Last Name</label>
                                <input id="last_name" type="text" class="form-control" name="last_name"
                                       value="{{ $user->last_name }}" required autofocus>

                                @if ($errors->has('last_name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('last_name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-lg-6 col-sm-6 col-md-6{{ $errors->has('old_password') ? ' has-error' : '' }}">
                                <label for="password">Old password</label>
                                <input id="password" type="password" class="form-control" name="old_password" value="">

                                @if ($errors->has('old_password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('old_password') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="col-lg-6 col-sm-6 col-md-6{{ $errors->has('password') ? ' has-error' : '' }}">
                                <label for="password-confirm">New password</label>
                                <input id="password-confirm" type="password" class="form-control" name="password"
                                       value="">
                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <button type="submit" class="btn btn-default btn-block btn-lg box-form-btn-green">
                            Save
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-12 col-xs-12 col-sm-12">

                <div class="col-lg-6" style="font-size:16px">
                    <h2>Subscription info</h2>

                    @if(!$user->subscription('main'))
                        An error occured while fetching subscription.
                        There is no active subscription on our database.
                        Please contact with support team. #9000
                    @else
                        @if($user->subscription('main')->ends_at)
                            Your subscription has been cancelled and will be ended at
                            <strong>{{($user->subscription('main')->ends_at)->format('d M Y')}}</strong>
                            . You won't be charged for next cycle. <br/>


                        @else
                            @if(!$user->stripe_id)
                                An error occured while fetching subscription.
                                Please contact with support team.  #8001
                            @elseif($user->stripe_id)

                                @if(!$user->subscription('main')->stripe_id)
                                    An error occured while fetching subscription. #8000-{{str_random(6)}}
                                @elseif($user->subscription('main')->stripe_id)
                                    @if(!$subscription)
                                        An error occured while fetching subscription.
                                        Please contact with support team. #9001-{{str_random(6)}}
                                    @else
                                        @if(!$subscription->plan->id)
                                            An error occured while fetching subscription.
                                            Please contact with support team. #9002-{{str_random(6)}}
                                        @else
                                            @if($isGrace)
                                                On grace period, valid to: <span
                                                        class="label label-success">{{$subscription->current_period_end ? ( date('d F Y',$subscription->current_period_end)) : ''}}</span>
                                                <a class="btn btn-success" style="margin-top: 5px"
                                                   href="/intapi/resume-subscription">Resume
                                                    subscription</a>
                                                <br>
                                            @else
                                                Next Billing:
                                                <span class="label label-success">{{$subscription->current_period_end ? ( date('d F Y',$subscription->current_period_end)) : ''}}</span>
                                            @endif
                                            <br><br>

                                            @if(!$isGrace)
                                                <a class="btn btn-warning" href="/intapi/cancel-subscription">Cancel
                                                    subscription</a>
                                                <br><br>
                                            @endif
                                        @endif
                                    @endif
                                @endif
                            @endif
                        @endif
                    @endif
                </div>

                {{--Renew Subscription--}}
                @if($user->subscription('main')->ends_at)
                    <div class="col-lg-6" style="font-size:16px">
                        <h2>Renew Subscription</h2>
                        <form class="form-horizontal" id="renewForm" method="POST"  action="/intapi/resume-subscription">


                            {{--Renew Form--}}
                            <div class="form-group">
                                    <div class="col-lg-7 col-sm-7 col-md-7">
                                        <label for="example2-card-number">Credit card info</label>
                                        {{--<input type="email" class="form-control" id="exampleInputEmail1">--}}
                                        <div id="example2-card-number"></div>
                                    </div>
                                    <div class="col-lg-5 col-sm-5 col-md-5">
                                        <div class="box-credit-card">
                                            <img src="/img/visa.png" alt="" class="img-responsive">
                                            <img src="/img/master.png" alt="" class="img-responsive">
                                            <img src="/img/master2.png" alt="" class="img-responsive">
                                            {{--<img src="/img/paypal.png" alt="" class="img-responsive">--}}
                                        </div>
                                    </div>
                            </div>
                            <div class="form-group">

                                    <div class="col-lg-4 col-sm-12">
                                        <label for="example2-card-expiry">Exp</label>
                                        <div id="example2-card-expiry"></div>
                                    </div>
                                    <div class="col-lg-4 col-sm-12">
                                        <label for="example2-card-cvc">CVC</label>
                                        <div id="example2-card-cvc"></div>
                                    </div>
                                    <div class="col-lg-4 col-sm-12 {{ $errors->has('coupon') ? ' has-error' : '' }}">
                                        <label for="coupon">Coupon</label>
                                        <div class="input-group">
                                            <input id="coupon" type="text" class="form-control" name="coupon" value="">
                                            <span id="validate-coupon"
                                                  class="input-group-addon btn btn-primary"><i
                                                        class="fa fa-check"></i> </span>
                                            <input style="display:none; visibility: hidden" id="coupon-validation"
                                                   type="text" class="form-control" name="coupon-validation" value="">
                                        </div>
                                        @if ($errors->has('coupon'))
                                            <span class="help-block">
                                        <strong>{{ $errors->first('coupon') }}</strong>

                                    </span>
                                        @endif
                                        {{--<br class="hidden-xs">--}}
                                        <span id="discount-message" style="font-weight: bold"></span>

                                    </div>

                            </div>
                            @if($thehotmealPlans)
                                <div class="form-group plans">
                                    <div class="col-lg-12">
                                        <label for="exampleInputEmail1">Choose subscription</label>
                                        <input type="hidden" name="plan" id="plan-input" value="">
                                    </div>
                                    @foreach($thehotmealPlans as $k=>$plan)
                                        <?php
                                        if ($k == 0) {
                                            $id = '';
                                        } elseif ($k == 1) {
                                            $id = '-two';
                                        } elseif ($k == 2) {
                                            $id = '-two';
                                        }
                                        ?>
                                        <div class="col-lg-3 col-sm-6 col-md-6 plan-btn-wrapper" >
                                            <a href="javascript:"
                                               onclick="selectPlan({{$plan->id}})"
                                               class="btn btn-default plan_selector plan_selector_{{$plan->id}}"
                                               id="btn-subscribe{{$id}}" style="padding: 0px;width: 112px; text-align: center" >
                                                <div class="plan-cost" style="margin-top:23px">${{$plan->cost/ $plan->month}}/mo.</div>
                                                @if($k != 0)
                                                    <p style=" ">${{($plan->cost)}} total for {{$plan->month}} months <br>
                                                        (save {{floor($plan->getSavingPercent($thehotmealPlans->first()->cost))}}
                                                        %)</p>
                                                @endif
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                                <button type="submit" style=""
                                        class="btn btn-default btn-block btn-lg box-form-btn-green">
                                    Renew Subscription
                                </button>
                                {{--<button type="submit" style="margin-top: 20px !important;" class="btn btn-large btn-primary">Renew Subscription</button>--}}


                        </form>

                        <script>
                            function selectPlan(planId) {
                                $("#plan-input").val(planId);
                                $(".plan_selector").removeClass('plan-selected');
                                $(".plan_selector_" + planId).addClass('plan-selected');
                            }

                            selectPlan({{$selectedPlan}});

                            $(document).ready(function () {
                                $("#validate-coupon").click(validateCoupon);

                                //$("#plan").change(updateTotalCost);

                                function validateCoupon() {
                                    var discountMessage = $('#discount-message');
                                    var coupon = $('#coupon').val();
                                    if (coupon == '') {
                                        discountMessage.text('');
                                    }
                                    else {
                                        $.ajax({
                                            type: "get",
                                            url: "/intapi/validate-coupon",
                                            data: {'coupon-code': coupon}
                                        })
                                            .done(function (data) {
                                                if (!data) {
                                                    discountMessage.text('Invalid coupon code');
                                                    return;
                                                }
                                                $('#plan option').each(function () {
                                                    $(this).remove();
                                                });
                                                var discount = data['discount'];
                                                var plan = data['planId'];
                                                var planName = data['planName'];
                                                var price = data['price'];

                                                $('#plan').append($('<option/>', {
                                                    value: plan,
                                                    text: planName + ' -  $' + price + ' USD',
                                                    price: price
                                                }));

                                                $('#coupon-validation').val(plan);
                                                discountMessage.text('$' + price + ' USD' + ' - ' + planName);
                                                selectPlan(plan);
                                                $(".plans").hide();
                                                //$('#total-cost').text('Total cost: $' + price);
                                            })
                                    }
                                }

                                /*function updateTotalCost() {
                                  var price = $('option:selected', this).attr('price');
                                  $('#total-cost').text('Total cost: $' + price);
                                }*/
                            })
                        </script>
                    </div>
                @endif
            </div>
        </div>

    </div>
@endsection
<style>
    .plan-selected {
        border-color: orange;
    }
</style>

@section('stripe')
    <script>
        function selectPlan(planId) {
            $("#plan-input").val(planId);
            $(".plan_selector").removeClass('plan-selected');
            $(".plan_selector_" + planId).addClass('plan-selected');
        }

        selectPlan({{$selectedPlan}});

        $(document).ready(function () {
            $("#validate-coupon").click(validateCoupon);

            //$("#plan").change(updateTotalCost);

            function validateCoupon() {
                var discountMessage = $('#discount-message');
                var coupon = $('#coupon').val();
                if (coupon == '') {
                    discountMessage.text('');
                }
                else {
                    $.ajax({
                        type: "get",
                        url: "/intapi/validate-coupon",
                        data: {'coupon-code': coupon}
                    })
                        .done(function (data) {
                            if (!data) {
                                discountMessage.text('Invalid coupon code');
                                return;
                            }
                            $('#plan option').each(function () {
                                $(this).remove();
                            });
                            var discount = data['discount'];
                            var plan = data['planId'];
                            var planName = data['planName'];
                            var price = data['price'];

                            $('#plan').append($('<option/>', {
                                value: plan,
                                text: planName + ' -  $' + price + ' USD',
                                price: price
                            }));

                            $('#coupon-validation').val(plan);
                            discountMessage.text('$' + price + ' USD' + ' - ' + planName);
                            selectPlan(plan);
                            $(".plans").hide();
                            //$('#total-cost').text('Total cost: $' + price);
                        })
                }
            }

            /*function updateTotalCost() {
              var price = $('option:selected', this).attr('price');
              $('#total-cost').text('Total cost: $' + price);
            }*/
        })
    </script>
    <script src="https://js.stripe.com/v3/"></script>
    <script type="text/javascript">
        var stripe = Stripe('{{config('services.stripe.key')}}');
        var elements = stripe.elements();
        var style = {
            base: {
                // Add your base input styles here. For example:
                fontSize: '16px',
                color: "#32325d",
            }
        };

        var cardNumber = elements.create('cardNumber', {
            classes: {
                'base': 'form-control'
            },
        });
        cardNumber.mount('#example2-card-number');


        var cardExpiry = elements.create('cardExpiry', {
            classes: {
                'base': 'form-control'
            },
        });
        cardExpiry.mount('#example2-card-expiry');

        var cardCvc = elements.create('cardCvc', {
            classes: {
                'base': 'form-control'
            },
        });
        cardCvc.mount('#example2-card-cvc');

        var elements = [
            'cardNumber',
            'cardCvc',
            'cardExpiry',
        ]
        // Create an instance of the card Element.
        //var card = elements.create(['cardNumber', 'cardExpiry', 'cardCvc'], {style: style});
        //console.log('card', card)
        // Add an instance of the card Element into the `card-element` <div>.
        //card.mount('#dropin-container');
        //console.log('card-mount', card)
        $('#total-cost').removeClass('hidden');

        cardNumber.addEventListener('change', function (event) {
            var $errorElement = $('#card-errors');
            if (event.error) $errorElement.html('<div class="alert alert-info">' + event.error.message + '</div>');
            if (event.empty == true || event.complete == false) {
                $('#payment-button').attr('disabled', 'disabled');
            } else {
                $errorElement.html('');
                $('#payment-button').removeAttr('disabled');
            }
        });

        //Step 3: Create a token to securely transmit card information
        // Create a token or display an error when the form is submitted.
        var form = document.getElementById('renewForm');
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            stripe.createToken(cardNumber).then(function (result) {
                if (result.error) {
                    // Inform the customer that there was an error.
                    var $errorElement = $('#card-errors');
                    $errorElement.html('<div class="alert alert-info">' + result.error.message + '</div>');
                } else {
                    // Send the token to your server.
                    stripeTokenHandler(result.token);
                }
            });
        });

        //Step 4: Submit the token and the rest of your form to your server
        function stripeTokenHandler(token) {
            console.log('token', token)
            // Insert the token ID into the form so it gets submitted to the server
            var form = document.getElementById('renewForm');
            var hiddenInput = document.createElement('input');
            hiddenInput.setAttribute('type', 'hidden');
            hiddenInput.setAttribute('name', 'stripeToken');
            hiddenInput.setAttribute('value', token.id);
            form.appendChild(hiddenInput);

            console.log(form)
            // Submit the form
            form.submit();
        }
    </script>
@endsection
