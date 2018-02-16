@extends('layouts.app')
@section('content')
    @extends('layouts.navbar')
    <div class="container">
        <div class="col-lg-12 col-xs-12 col-sm-12" id="box-user-profile">
            <div class="col-lg-6 col-xs-12 col-sm-6">
                <h2>Hi, {{$user->first_name}}</h2>
                <h5>Daily Calorie Goal: {{$user->calorie_goal}} Calories per day</h5>
            </div>
            <div class="col-lg-6 col-xs-12 col-sm-6">
                <p class="date-profile">Today's Date : {{$date}}</p>
            </div>
        </div>
        <div class="col-lg-12 col-xs-12 col-sm-12" id="box-menu-profile">
            <div class="col-lg-4 col-xs-12 col-sm-3 col-md-4">
                <h5>This Week's Menu</h5>
            </div>
            <div class="col-lg-5 col-xs-12 col-sm-5 col-md-5">
                <a href="/grocery-list" class="btn btn-default" id="btn-list">View this Week's Grocery List <i
                            class="fa fa-server"></i></a>
            </div>
            <div class="col-lg-3 col-xs-12 col-sm-4 col-md-3">
                <a href="#" class="btn btn-default" id="btn-list-week">Add New Week <i class="fa fa-plus"></i></a>
            </div>
        </div>

        <!--CARD -->
        <div id="profile-client">
        </div>
    </div>
    <script>
        window.startDate = '{{$startDate}}';
        window.weekPlanId = '{{$weekPlanId}}';
    </script>
    <script src="{{ URL::asset('js/ProfileClient.js')}}"></script>

@endsection