<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    {!!Html::style('css/app.css')!!}
    {!!Html::style('css/style.css')!!}

    @yield('head')

</head>
<body>
    <div id="app">
        <nav class="navbar navbar-inverse fixed-top bg-inverse">
            <div class="container-fluid mx-0">
                <div class="navbar-header">
                    <button class="navbar-toggler collapsed hidden-md-up" type="button" data-toggle="collapse" data-target="#sidebar-container" aria-controls="sidebar-container" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <!-- Branding Image -->
                    <a class="navbar-brand py-3" href="{{url('/')}}">
                        <div id="main-logo-container">
                            <img src="{{url('img/logo.png')}}"/>
                        </div>
                        <span class="hidden-xs-down">
                            {{config('app.name', 'Laravel')}}
                        </span>
                    </a>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav float-right py-3 d-inline-block   ">
                        <!-- Authentication Links -->
                        @if (Auth::guest())
                            <li><a href="{{route('login')}}">Login</a></li>
                            <!--<li><a href="{{route('register')}}">Register</a></li>-->
                        @else
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu px-2" style="margin-left: -2rem;" role="menu">

                                    <li>
                                        <a href="{{route('my-profile/overview')}}">My Profile</a>
                                    </li>
                                    <li>
                                        <a href="{{route('logout')}}"
                                           onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                            Logout
                                        </a>
                                        <form id="logout-form" action="{{route('logout')}}" method="POST" style="display: none;">
                                            {{ csrf_field() }}
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </nav>

        <div style="height: 78px;"></div>

        @yield('content')

    </div>

    <!-- Scripts -->
    {!!Html::script('https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js')!!}
    {!!Html::script('js/app.js')!!}
    {!!Html::script('https://code.jquery.com/jquery-3.1.1.min.js')!!}
    {!!Html::script('js/functions.js')!!}
    {!!Html::script('https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js')!!}

    @if(Session::has('alert-success'))
        <script type="text/javascript">
            $(document).ready(function(){
                var html = '<div class="alert alert-success t-2" role="alert">{{Session::get("alert-success")}}</div>';
                $("body").append(html);
                setTimeout(function(){
                    $(".alert").fadeOut(2000);
                }, 3000);
            })
        </script>
    @endif
    @if(Session::has('alert-danger'))
        <script type="text/javascript">
            $(document).ready(function(){
                var html = '<div class="alert alert-danger t-2" role="alert">{{Session::get("alert-danger")}}</div>';
                $("body").append(html);
                setTimeout(function(){
                    $(".alert").fadeOut(2000);
                }, 3000);
            })
        </script>
    @endif

    <!-- Ajax -->
    @yield('ajax')

    <!-- Additionnal Script -->
    @yield('script')

</body>
</html>
