@php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Utility;
@endphp

@extends('layouts.app')

@section('content')
    <div class="container-fluid py-3">
        <div class="row">
            <div class="col-3 col-md-2 hidden-xs-down"></div>
            <nav id="sidebar" class="col-3 col-md-2 hidden-xs-down bg-faded sidebar">
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item">
                        <a 
                            @if($title == 'overview')
                                class="nav-link active" 
                            @else
                                class="nav-link" 
                            @endif
                            href="overview">
                            Overview
                        </a>
                    </li>
                    <li class="nav-item">
                        <a 
                            @if($title == 'details')
                                class="nav-link active" 
                            @else
                                class="nav-link" 
                            @endif
                            href="details">
                            Details
                        </a>
                    </li>
                    <li class="nav-item">
                        <a 
                            @if($title == 'transaction')
                                class="nav-link active" 
                            @else
                                class="nav-link" 
                            @endif
                            href="transaction">
                            Transaction
                        </a>
                    </li>
                    <li class="nav-item">
                        <a 
                            @if($title == 'category')
                                class="nav-link active" 
                            @else
                                class="nav-link" 
                            @endif
                            href="category">
                            Category
                        </a>
                    </li>
                    <li class="nav-item">
                        <a 
                            @if($title == 'account')
                                class="nav-link active" 
                            @else
                                class="nav-link" 
                            @endif
                            href="account">
                            Account
                        </a>
                    </li>
                </ul>
            </nav>

            <main class="col-9 col-md-10">
                <section class="container">
                    <h1 class="mb-3">{{Utility::deslugify($title, true)}}</h1>
                    @yield('section')
                </section> 
            </main>
        </div>
    </div>
@stop